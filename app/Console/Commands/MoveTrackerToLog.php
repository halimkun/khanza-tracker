<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MoveTrackerToLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracker:move-to-log {--month= : Month to process (e.g. 2024-10)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move tracker data to log files grouped by year and month';

    /**
     * The console command help text.
     *
     * @var string
     */
    protected $help = 'This command moves tracker data to log files grouped by year and month.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $month = $this->option('month') ?? date('Y-m');

        // Validasi format bulan
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error("Invalid month format. Please use 'YYYY-MM'.");
            return 1;
        }

        $this->info("Processing month: $month");
        $year = date('Y', strtotime($month));

        // Ambil semua data dari tabel tracker untuk bulan tertentu
        $trackers = DB::table('trackersql')
            ->whereYear('tanggal', date('Y', strtotime($month)))
            ->whereMonth('tanggal', date('m', strtotime($month)))
            ->get();

        // count
        $count = $trackers->count();
        $this->info("Total data: $count");

        // Jika tidak ada data, keluar
        if ($count == 0) {
            $this->info('No data to process');
            return 0;
        }

        // Tentukan nama file log berdasarkan bulan
        $filename = "{$month}.log";

        // Format data untuk disimpan di file log
        $logData = "";
        foreach ($trackers as $record) {
            // $logData .= "[{$record->tanggal}] local.{$this->getType($record->sqle)}: User Action Tracked. {\"user\": \"{$record->usere}\", \"query\": \"{$record->sqle}\"}\n";
            $logData .= "[{$record->tanggal}] local.{$this->getType($record->sqle)}: [petugas] {$record->usere}, [query] {$record->sqle}\n";
        }

        try {
            // Tentukan direktori berdasarkan tahun
            $directory = "/home/sysadmin/khanzaLog/{$year}";

            // Cek apakah direktori sudah ada
            if (!is_dir($directory)) {
                // Jika belum ada, buat direktori dengan permission 0755
                mkdir($directory, 0755, true);
            }

            // Tentukan nama file lengkap
            $filePath = "{$directory}/{$filename}";

            // Tulis data ke file, append jika file sudah ada
            file_put_contents($filePath, $logData, FILE_APPEND);

            // Informasi bahwa file berhasil ditulis
            $this->info("Log file created: {$filePath}");
        } catch (\Exception $e) {
            // Tangani error penulisan file
            $this->error("Error writing to log file: " . $e->getMessage());
            return 1;
        }

        // Informasi tentang file yang berhasil dibuat
        $this->info("Log file created: {$filename}");

        return 0;
    }

    private function getType($sqle)
    {
        if (strpos($sqle, 'insert') !== false) {
            return 'info';
        } elseif (strpos($sqle, 'update') !== false) {
            return 'warning';
        } elseif (strpos($sqle, 'delete') !== false) {
            return 'error';
        } elseif (strpos($sqle, 'truncate') !== false) {
            return 'critical';
        } else {
            return 'alert';
        }
    }
}
