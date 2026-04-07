<?php
/**
 * ============================================
 * API BOOKING BESOK (booking-besok.php)
 * ============================================
 * Endpoint untuk mengambil semua booking yang
 * tanggalnya = besok (hari ini + 1 hari)
 * Return data dalam format JSON
 */

header('Content-Type: application/json; charset=utf-8');

// Koneksi database
require_once 'config/db.php';

try {
    $db = getDB();
    
    // Hitung tanggal besok
    $tanggalBesok = date('Y-m-d', strtotime('+1 day'));
    
    // Query booking besok dengan JOIN ke users dan paket_foto
    $sql = "
        SELECT 
            u.nama_lengkap AS nama,
            u.nomor_telepon AS telepon,
            b.tanggal_booking AS tanggal,
            b.jam_mulai AS jam,
            p.nama_paket AS paket
        FROM booking b
        INNER JOIN users u ON b.id_user = u.id_user
        INNER JOIN paket_foto p ON b.id_paket = p.id_paket
        WHERE b.tanggal_booking = ?
        AND b.status_booking NOT IN ('dibatalkan')
        ORDER BY b.jam_mulai ASC
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$tanggalBesok]);
    $bookings = $stmt->fetchAll();
    
    // Format response
    $response = [];
    if (!empty($bookings)) {
        foreach ($bookings as $booking) {
            $response[] = [
                'nama' => $booking['nama'],
                'telepon' => $booking['telepon'],
                'tanggal' => $booking['tanggal'],
                'jam' => substr($booking['jam'], 0, 5), // Format HH:MM
                'paket' => $booking['paket']
            ];
        }
    }
    
    // Output JSON
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Error handling
    http_response_code(500);
    echo json_encode([
        'error' => 'Gagal mengambil data booking',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
