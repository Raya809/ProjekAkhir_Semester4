<?php
// config/core.php

class Core {
    // KUNCI RAHASIA INI HARUS KUAT DAN JANGAN PERNAH DIBAGIKAN!
    // GANTI DENGAN STRING YANG BENAR-BENAR ACAK DAN PANJANG
    public static $SECRET_KEY = "ini_kunci_rahasia_sangat_kuat_untuk_proyek_animelist_anda_silakan_ganti_dengan_yang_lebih_kompleks_dan_unik_misalnya_dengan_generator_random_string";
    
    public static $ISSUER = "http://localhost/ANIMELIST-API/"; // Ganti jika URL API Anda berbeda
    public static $AUDIENCE = "http://localhost:3000"; // URL frontend Anda
    public static $ALGORITHM = ['HS256'];
    public static $JWT_EXPIRE_SECONDS = 3600; // Token akan expired dalam 1 jam (3600 detik)
}
?>