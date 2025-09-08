<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AsalGerbangController extends Controller
{
    /** Nama koneksi custom */
    private const CONN = 'travoy_db_history';

    /**
     * Set koneksi sekali, purge & reconnect agar pasti terpakai.
     */
    private function ensureConn(): void
    {
        if (!config('database.connections.' . self::CONN)) {
            Config::set('database.connections.' . self::CONN, [
                'driver'    => 'mysql',
                'host'      => env('TRAVOY_DB_HOST', '172.16.39.109'),
                'port'      => env('TRAVOY_DB_PORT', 14045),
                'database'  => env('TRAVOY_DB_DATABASE', 'travoy_db_history'),
                'username'  => env('TRAVOY_DB_USERNAME', 'jmto'),
                'password'  => env('TRAVOY_DB_PASSWORD', '@jmt02024!#'),
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
            ]);
        }

        DB::purge(self::CONN);
        DB::reconnect(self::CONN);
        DB::connection(self::CONN)->getPdo(); // sanity check
    }

    /** Helper table builder */
    private function table()
    {
        $this->ensureConn();
        return DB::connection(self::CONN)->table('asal_gerbang');
    }

    public function index()
    {
        return view('pages.resi.AsalGerbang.index', [
            'columns' => [
                ['title' => 'No', 'data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false],
                ['title' => 'Nama Ruas', 'data' => 'nama_ruas', 'orderable' => true, 'searchable' => true],
                ['title' => 'Nama Asal Gerbang', 'data' => 'nama_asal_gerbang', 'orderable' => true, 'searchable' => true],
                ['title' => 'Action', 'data' => 'action', 'orderable' => false, 'searchable' => false],
            ]
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $q = $this->table();

            if ($request->filled('ruas_id')) {
                $q->where('id_ruas', $request->ruas_id);
            }

            return DataTables::of($q)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $idAsal   = htmlspecialchars($row->id_asal_gerbang, ENT_QUOTES, 'UTF-8');
                    $ruasId   = htmlspecialchars($row->id_ruas ?? '', ENT_QUOTES, 'UTF-8');
                    $ruasNama = htmlspecialchars($row->nama_ruas ?? '', ENT_QUOTES, 'UTF-8');

                    $btnEdit = sprintf(
                        '<button type="button"
                                class="bg-yellow-500 text-white rounded-md px-2 py-1 btn-edit"
                                data-id="%1$s"
                                data-ruas-id="%2$s"
                                data-ruas-nama="%3$s">Edit</button>',
                        $idAsal, $ruasId, $ruasNama
                    );

                    $btnDelete = sprintf(
                    '<button type="button" class="bg-red-500 text-white rounded-md px-2 py-1"
                            onclick="deleteData(%s, %s, %s)">Delete</button>',
                    htmlspecialchars(json_encode($row->id_asal_gerbang), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(json_encode($row->id_ruas), ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(json_encode($row->nama_ruas), ENT_QUOTES, 'UTF-8')
                    );


                    return $btnEdit . ' ' . $btnDelete;
                })
                ->rawColumns(['action'])
                ->make(true);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_asal_gerbang'   => ['required'],
            'ruas_id'           => ['required'],
            'nama_asal_gerbang' => ['required', 'string', 'max:255'],
            'nama_ruas'         => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->table()->insert([
                'id_asal_gerbang'   => $validated['id_asal_gerbang'],
                'id_ruas'           => $validated['ruas_id'],
                'nama_asal_gerbang' => $validated['nama_asal_gerbang'],
                'nama_ruas'         => $validated['nama_ruas'] ?? null,
            ]);

            return response()->json(['message' => 'success', 'error' => false], 201);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $id = $request->id;
            $ruas_id = $request->ruas_id;
            $ruas_nama = $request->ruas_nama;
            $row = $this->table()->where('id_asal_gerbang', $id)->where('id_ruas', $ruas_id)->where('nama_ruas', $ruas_nama)->first();

            if (!$row) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            return response()->json(['data' => $row]);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }

    /** Fitur Edit */

    public function update(Request $request)
    {

        $id = $request->original_id;
        $original_ruas_id = $request->original_ruas_id;
        $original_ruas_nama = $request->original_ruas_nama;
        // 1) Pastikan koneksi siap dulu sebelum validasi
        $this->ensureConn();

        // 2) Validasi pakai "connection.table"
        $validated = $request->validate([
            'id_asal_gerbang' => [
                'required',
                Rule::unique(self::CONN . '.asal_gerbang', 'id_asal_gerbang')
                    ->ignore($id, 'id_asal_gerbang'),
            ],
            'ruas_id'           => ['required'],
            'nama_asal_gerbang' => ['required', 'string', 'max:255'],
            'nama_ruas'         => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $exists = $this->table()->where('id_asal_gerbang', $id)->exists();
            if (!$exists) {
                return response()->json(['message' => 'Data tidak ditemukan'], 404);
            }

            $this->table()->where('id_asal_gerbang', $id)->where('id_ruas', $original_ruas_id)->where('nama_ruas', $original_ruas_nama)->update([
                'id_asal_gerbang'   => $validated['id_asal_gerbang'],
                'id_ruas'           => $validated['ruas_id'],
                'nama_asal_gerbang' => $validated['nama_asal_gerbang'],
                'nama_ruas'         => $validated['nama_ruas'] ?? null,
            ]);

            return response()->json(['message' => 'updated', 'error' => false], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => true], 500);
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->id;
        $id_ruas = $request->id_ruas;
        $nama_ruas = $request->nama_ruas;
        if (!$id || !$id_ruas || !$nama_ruas) {
            return response()->json(['message' => 'Parameter id wajib'], 422);
        }

        $deleted = $this->table()->where('id_asal_gerbang', $id)->where('id_ruas', $id_ruas)->where('nama_ruas', $nama_ruas)->delete();
        if (!$deleted) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        return response()->json(['message' => 'success', 'error' => false], 200);
    }
}
