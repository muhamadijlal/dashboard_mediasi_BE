<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trans Detail Export</title>
</head>
<body>
    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Gerbang ID</th>
            <th>Shift</th>
            <th>Metoda Bayar</th>
            <th>Metoda Bayar ID</th>
            <th>Jumlah Tarif Integrator</th>
            <th>Jumlah Tarif Mediasi</th>
            <th>Jumlah Data Integrator</th>
            <th>Jumlah Data Mediasi</th>
            <th>Selisih</th>
        </tr>
        </thead>
        <tbody>
        @foreach($dataCompare as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->tanggal }}</td>
                <td>{{ $row->gerbang_id }}</td>
                <td>{{ $row->shift }}</td>
                <td>{{ $row->metoda_bayar }}</td>
                <td>{{ $row->metoda_bayar_name }}</td>
                <td>{{ $row->jumlah_tarif_integrator }}</td>
                <td>{{ $row->jumlah_tarif_mediasi }}</td>
                <td>{{ $row->jumlah_data_integrator }}</td>
                <td>{{ $row->jumlah_data_mediasi }}</td>
                <td>{{ $row->selisih }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>