@php
    // Ganti data rekening di bawah ini sesuai rekening Saung Liwet.
    $rek = ['bank' => 'BCA', 'nomor' => '0000000000', 'atas_nama' => 'Saung Liwet'];
@endphp
<div class="transfer-box" style="background:#fff;border:1px solid #d1d5db;border-radius:10px;padding:12px;margin:8px 0;">
    <div style="font-size:12px;color:#6b7280;">Bank</div>
    <div style="font-weight:700;">{{ $rek['bank'] }}</div>
    <div style="font-size:12px;color:#6b7280;margin-top:8px;">Nomor rekening</div>
    <div style="font-weight:700;font-size:18px;letter-spacing:1px;">{{ $rek['nomor'] }}</div>
    <div style="font-size:12px;color:#6b7280;margin-top:8px;">Atas nama</div>
    <div style="font-weight:700;">{{ $rek['atas_nama'] }}</div>
    @isset($nominal)
        <div style="font-size:12px;color:#6b7280;margin-top:8px;">Nominal transfer</div>
        <div style="font-weight:700;">Rp {{ number_format($nominal, 0, ',', '.') }}</div>
    @endisset
</div>