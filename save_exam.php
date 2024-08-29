<?php
// Hata raporlamayı etkinleştir
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log dosyası
$logFile = 'exam_save_log.txt';

// Gelen JSON verisini al
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Veritabanı bağlantı bilgileri
$db_host = '127.0.0.1';
$db_user = 'root';
$db_password = 'root';
$db_name = 'mysql';
$db_port = 3306;

// Log fonksiyonu
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Güncellenmiş veri işleme fonksiyonu
function processData($data, $keys) {
    $value = $data;
    foreach ($keys as $key) {
        if (isset($value[$key])) {
            $value = $value[$key];
        } else {
            return 0; // Eğer herhangi bir anahtar bulunamazsa 0 döndür
        }
    }
    return empty($value) ? 0 : intval($value);
}


logMessage("Veri alındı: " . print_r($data, true));

try {
    // Veritabanına bağlan
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);

    if ($mysqli->connect_error) {
        throw new Exception("Veritabanı bağlantı hatası: " . $mysqli->connect_error);
    }

    logMessage("Veritabanı bağlantısı başarılı");

    // Veriyi işle
    $tytTurkceDogru = processData($data, ['tyt', 'turkce', 'dogru']);
    $tytTurkceYanlis = processData($data, ['tyt', 'turkce', 'yanlis']);
    $tytSosyalDogru = processData($data, ['tyt', 'sosyal', 'b.', 'dogru']);
    $tytSosyalYanlis = processData($data, ['tyt', 'sosyal', 'b.', 'yanlis']);
    $tytMatematikDogru = processData($data, ['tyt', 't.', 'matematik', 'dogru']);
    $tytMatematikYanlis = processData($data, ['tyt', 't.', 'matematik', 'yanlis']);
    $tytFenDogru = processData($data, ['tyt', 'fen', 'b.', 'dogru']);
    $tytFenYanlis = processData($data, ['tyt', 'fen', 'b.', 'yanlis']);
    
    $aytMatematikDogru = processData($data, ['ayt', 'matematik', 'dogru']);
    $aytMatematikYanlis = processData($data, ['ayt', 'matematik', 'yanlis']);
    $aytFizikDogru = processData($data, ['ayt', 'fizik', 'dogru']);
    $aytFizikYanlis = processData($data, ['ayt', 'fizik', 'yanlis']);
    $aytKimyaDogru = processData($data, ['ayt', 'kimya', 'dogru']);
    $aytKimyaYanlis = processData($data, ['ayt', 'kimya', 'yanlis']);
    $aytBiyolojiDogru = processData($data, ['ayt', 'biyoloji', 'dogru']);
    $aytBiyolojiYanlis = processData($data, ['ayt', 'biyoloji', 'yanlis']);
    $aytEdebiyatDogru = processData($data, ['ayt', 'turk', 'dili', 've', 'edebiyati', 'dogru']);
    $aytEdebiyatYanlis = processData($data, ['ayt', 'turk', 'dili', 've', 'edebiyati', 'yanlis']);
    $aytTarih1Dogru = processData($data, ['ayt', 'tarih-1', 'dogru']);
    $aytTarih1Yanlis = processData($data, ['ayt', 'tarih-1', 'yanlis']);
    $aytCografya1Dogru = processData($data, ['ayt', 'cografya-1', 'dogru']);
    $aytCografya1Yanlis = processData($data, ['ayt', 'cografya-1', 'yanlis']);
    $aytTarih2Dogru = processData($data, ['ayt', 'tarih-2', 'dogru']);
    $aytTarih2Yanlis = processData($data, ['ayt', 'tarih-2', 'yanlis']);
    $aytCografya2Dogru = processData($data, ['ayt', 'cografya-2', 'dogru']);
    $aytCografya2Yanlis = processData($data, ['ayt', 'cografya-2', 'yanlis']);
    $aytFelsefeDogru = processData($data, ['ayt', 'felsefe', 'grubu', 'dogru']);
    $aytFelsefeYanlis = processData($data, ['ayt', 'felsefe', 'grubu', 'yanlis']);
    $aytDinDogru = processData($data, ['ayt', 'din', 'kulturu', 've', 'ahlak', 'bilgisi', 'dogru']);
    $aytDinYanlis = processData($data, ['ayt', 'din', 'kulturu', 've', 'ahlak', 'bilgisi', 'yanlis']);
    
    $ydtDogru = processData($data, ['ydt', 'dogru']);
    $ydtYanlis = processData($data, ['ydt', 'yanlis']);

    // Sorguyu hazırla
    $query = "INSERT INTO wp_yks_exams (user_id, exam_name, exam_date, 
              tyt_turkce_dogru, tyt_turkce_yanlis, tyt_sosyal_b_dogru, tyt_sosyal_b_yanlis,
              tyt_t_matematik_dogru, tyt_t_matematik_yanlis, tyt_fen_b_dogru, tyt_fen_b_yanlis,
              ayt_matematik_dogru, ayt_matematik_yanlis, ayt_fizik_dogru, ayt_fizik_yanlis,
              ayt_kimya_dogru, ayt_kimya_yanlis, ayt_biyoloji_dogru, ayt_biyoloji_yanlis,
              ayt_turk_dili_ve_edebiyati_dogru, ayt_turk_dili_ve_edebiyati_yanlis,
              ayt_tarih1_dogru, ayt_tarih1_yanlis, ayt_cografya1_dogru, ayt_cografya1_yanlis,
              ayt_tarih2_dogru, ayt_tarih2_yanlis, ayt_cografya2_dogru, ayt_cografya2_yanlis,
              ayt_felsefe_grubu_dogru, ayt_felsefe_grubu_yanlis, ayt_dkab_dogru, ayt_dkab_yanlis,
              ydt_dogru, ydt_yanlis)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        throw new Exception("Sorgu hazırlama hatası: " . $mysqli->error);
    }

    // Parametreleri bağla
    $stmt->bind_param("issiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii",
        $userId, $data['examName'], $data['examDate'],
        $tytTurkceDogru, $tytTurkceYanlis, $tytSosyalDogru, $tytSosyalYanlis,
        $tytMatematikDogru, $tytMatematikYanlis, $tytFenDogru, $tytFenYanlis,
        $aytMatematikDogru, $aytMatematikYanlis, $aytFizikDogru, $aytFizikYanlis,
        $aytKimyaDogru, $aytKimyaYanlis, $aytBiyolojiDogru, $aytBiyolojiYanlis,
        $aytEdebiyatDogru, $aytEdebiyatYanlis, $aytTarih1Dogru, $aytTarih1Yanlis,
        $aytCografya1Dogru, $aytCografya1Yanlis, $aytTarih2Dogru, $aytTarih2Yanlis,
        $aytCografya2Dogru, $aytCografya2Yanlis, $aytFelsefeDogru, $aytFelsefeYanlis,
        $aytDinDogru, $aytDinYanlis, $ydtDogru, $ydtYanlis
    );

    // Kullanıcı ID'sini 1 olarak varsayalım (gerçek uygulamada oturum bilgisinden alınmalıdır)
    $userId = 1;

    logMessage("Sorgu ve parametreler hazırlandı");


    // Sorguyu çalıştır
    if ($stmt->execute()) {
        logMessage("Sınav başarıyla kaydedildi. Etkilenen satır sayısı: " . $stmt->affected_rows);
        echo json_encode(['success' => true, 'message' => 'Sınav başarıyla kaydedildi.']);
    } else {
        throw new Exception("Sorgu çalıştırma hatası: " . $stmt->error);
    }

    $stmt->close();
    $mysqli->close();

} catch (Exception $e) {
    logMessage("Hata: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>