<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\DownloadExcelAdmin;
use Illuminate\Support\Facades\Storage;

class DeleteReportFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete-report-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $data = DownloadExcelAdmin::where('created_at', '<', Carbon::now()->subDays(7))->get();
        foreach($data as $val) {
            Storage::delete("sales_report/".$val->excel_file);
            $val->delete();
        }

        return Command::SUCCESS;
    }
}
