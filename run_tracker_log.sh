#!/bin/bash

# NOTE: Pastikan perintah artisan berjalan di dalam folder project Laravel
# NOTE: Sesuaikan bulan dengan data yang akan dipindahkan ke log (01-12)
# NOTE: Sesuaikan tahun dengan data yang akan dipindahkan ke log (YYYY)

for month in {01..6}
do
    # Jalankan perintah artisan dengan PHP 8.2 dan parameter bulan
    php8.2 artisan tracker:move-to-log --month=2024-$month
done
