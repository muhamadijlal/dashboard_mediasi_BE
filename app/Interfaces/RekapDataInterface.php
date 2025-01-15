<?php

namespace App\Interfaces;

interface RekapDataInterface
{
    /**
     * method getDataTransakiDetail untuk mengambil data detail transaksi
     * 
     * @access  public
     * @param string $ruas_id
     * @param string $gerbang_id
     * @param string|null $start_date
     * @param string|null $end_date
     * @param int|10 $limit
     * @method  POST api/transaksi_detil/{$ruas_id}/{$gerbang_id}/{?page}
     */
    public function getDataTransakiDetail(string $ruas_id, string $gerbang_id, ?string $start_date = null, ?string $end_date = null, ?int $limit = 10);

    /**
     * method getDataRekapAT4 untuk mengambil data rekap at4
     * 
     * @access  public
     * @param string $ruas_id
     * @param string $gerbang_id
     * @param string|null $start_date
     * @param int|10 $limit
     * @method  POST api/rekap_at4/{$ruas_id}/{$gerbang_id}/{?page}
     */
    public function getDataRekapAT4(string $ruas_id, string $gerbang_id, ?string $start_date = null, ?string $end_date = null, ?int $limit = 10);

    /**
     * method getDataRekapPendapatan untuk mengambil data rekap pendapatan
     * 
     * @access  public
     * @param string $ruas_id
     * @param string $gerbang_id
     * @param string|null $start_date
     * @param string|null $end_date
     * @param int|10 $limit
     * @method  POST api/rekap_pendapatan/{$ruas_id}/{$gerbang_id}/{?page}
     */
    public function getDataRekapPendapatan(string $ruas_id, string $gerbang_id, ?string $start_date = null, ?string $end_date = null, ?int $limit = 10);
}
