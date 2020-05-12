<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User\Study\Note;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class reSyncV2Notes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reSyncV2:notes {note_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re Sync the Notes that are not edited with the V2 Database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $note_id = $this->argument('note_id');
        echo "\n" . Carbon::now() . ': v2 to v4 notes re sync started.';
        $db_v2_connection = DB::connection('dbp_users_v2');
        $db_users_connection = DB::connection('dbp_users');

        $chunk_size = 25;
        do {
            $v4_notes = $db_users_connection
                ->table('user_notes')
                ->where('v2_id', '!=', 0)
                ->where('bookmark', '=', 0)
                ->when($note_id, function ($query, $note_id) {
                    return $query->whereId($note_id);
                })
                ->orderBy('v2_id')->limit($chunk_size)->get();
            $v2_ids = $v4_notes->pluck('v2_id');
            $v4_updated_dates = $v4_notes->pluck('updated_at', 'v2_id');
            $v4_created_dates = $v4_notes->pluck('created_at', 'v2_id');
            $v2_notes = $db_v2_connection->table('note')
                ->select(['id', 'created', 'updated', 'note'])->whereIn('id', $v2_ids)->get();
            $synced = 0;
            foreach ($v2_notes as $v2_note) {
                $note_changed = true;
                // Different creation dates on v4 -> v2
                if ($v4_created_dates[$v2_note->id] !== $v2_note->created) {
                    // Same v4 updated_at and v4 created_at values means no change
                    if ($v4_created_dates[$v2_note->id] === $v4_updated_dates[$v2_note->id]) {
                        $note_changed = false;
                    }
                    // Same update dates on V4 -> v2 means no change
                } elseif ($v4_updated_dates[$v2_note->id] === $v2_note->updated) {
                    $note_changed = false;
                }

                // If note is not changed re sync
                if (!$note_changed) {
                    $db_users_connection->table('user_notes')
                        ->where('v2_id', $v2_note->id)
                        ->update([
                            'notes' => encrypt($v2_note->note),
                            'updated_at'  => Carbon::createFromTimeString($v2_note->updated),
                            'bookmark' => 1,
                        ]);
                    $synced++;
                    echo "\n" . Carbon::now() . ': Re synced ' . $synced . '/' . $chunk_size . ' v2 notes.';
                }
            }
            $v4_ids = $v4_notes->pluck('id');
            $db_users_connection->table('user_notes')->whereIn('id', $v4_ids)->update(['bookmark' => 1]);
            $remaining = $db_users_connection
                ->table('user_notes')
                ->where('v2_id', '!=', 0)
                ->when($note_id, function ($query, $note_id) {
                    return $query->whereId($note_id);
                })
                ->where('bookmark', '=', 0)->count();
            echo "\n" . Carbon::now() . ': ' . $remaining . ' remaining to process.';
        } while (!$v4_notes->isEmpty());

        echo "\n" . Carbon::now() . ": v2 to v4 notes re sync finalized.\n";
    }
}