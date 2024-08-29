<?php
header('Content-Type: application/json');

// Function to log debug information
function debug_log($message) {
    error_log(print_r($message, true), 3, "debug.log");
}

// Read the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// // Log the received data for debugging
// debug_log("Received data:");
// debug_log($data);

function calculateScores($data) {
    // Log the data within the function
    // debug_log("Data in calculateScores:");
    // debug_log($data);
    
    try {
        $tytNet = calculateTYTNet($data);
        $aytNetScores = calculateAYTNet($data);
        $ydtNet = calculateYDTNet($data);
        $obp = calculateOBP($data['diploma_notu'] ?? 0, isset($data['onceki_yerlestirme']));

        $tytScore = calculateTYTScore($tytNet);
        $aytScores = calculateAYTScores($aytNetScores, $ydtNet);

        $placementScores = [
            'tyt' => ['ham' => $tytScore, 'yer' => $tytScore + ($obp * 0.12)],
            'say' => ['ham' => $tytScore * 0.4 + $aytScores['say'] * 0.6, 'yer' => $tytScore * 0.4 + $aytScores['say'] * 0.6 + ($obp * 0.12)],
            'ea' => ['ham' => $tytScore * 0.4 + $aytScores['ea'] * 0.6, 'yer' => $tytScore * 0.4 + $aytScores['ea'] * 0.6 + ($obp * 0.12)],
            'soz' => ['ham' => $tytScore * 0.4 + $aytScores['soz'] * 0.6, 'yer' => $tytScore * 0.4 + $aytScores['soz'] * 0.6 + ($obp * 0.12)],
            'dil' => ['ham' => $aytScores['dil'], 'yer' => $aytScores['dil'] + ($obp * 0.12)],
        ];

        foreach ($placementScores as &$score) {
            $score['ham'] = max(0, round($score['ham'], 3));
            $score['yer'] = max(0, round($score['yer'], 3));
        }
        // Log the calculated scores
        // debug_log("Calculated scores:");
        // debug_log($placementScores);

        return $placementScores;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return ['error' => 'An error occurred during calculation.'];
    }
}

function calculateNet($dogru, $yanlis) {
    $dogru = intval($dogru);  // Convert to integer
    $yanlis = intval($yanlis);  // Convert to integer
    return max(-20, $dogru - ($yanlis * 0.25)); // Allow negative nets, but limit to -20
}

function calculateTYTNet($data) {
    return [
        'turkce' => calculateNet($data['tyt_turkce_dogru'] ?? 0, $data['tyt_turkce_yanlis'] ?? 0),
        'sosyal' => calculateNet($data['tyt_sosyal_b._dogru'] ?? 0, $data['tyt_sosyal_b._yanlis'] ?? 0),
        'matematik' => calculateNet($data['tyt_t._matematik_dogru'] ?? 0, $data['tyt_t._matematik_yanlis'] ?? 0),
        'fen' => calculateNet($data['tyt_fen_b._dogru'] ?? 0, $data['tyt_fen_b._yanlis'] ?? 0),
    ];
}

function calculateAytNet($data) {
    // debug_log("AYT Data:");
    // debug_log($data);
    return [
        'matematik' => calculateNet($data['ayt_matematik_dogru'] ?? 0, $data['ayt_matematik_yanlis'] ?? 0),
        'fizik' => calculateNet($data['ayt_fizik_dogru'] ?? 0, $data['ayt_fizik_yanlis'] ?? 0),
        'kimya' => calculateNet($data['ayt_kimya_dogru'] ?? 0, $data['ayt_kimya_yanlis'] ?? 0),
        'biyoloji' => calculateNet($data['ayt_biyoloji_dogru'] ?? 0, $data['ayt_biyoloji_yanlis'] ?? 0),
        'edebiyat' => calculateNet($data['ayt_turk_dili_ve_edebiyati_dogru'] ?? 0, $data['ayt_turk_dili_ve_edebiyati_yanlis'] ?? 0),
        'tarih1' => calculateNet($data['ayt_tarih-1_dogru'] ?? 0, $data['ayt_tarih-1_yanlis'] ?? 0),
        'cografya1' => calculateNet($data['ayt_cografya-1_dogru'] ?? 0, $data['ayt_cografya-1_yanlis'] ?? 0),
        'tarih2' => calculateNet($data['ayt_tarih-2_dogru'] ?? 0, $data['ayt_tarih-2_yanlis'] ?? 0),
        'cografya2' => calculateNet($data['ayt_cografya-2_dogru'] ?? 0, $data['ayt_cografya-2_yanlis'] ?? 0),
        'felsefe' => calculateNet($data['ayt_felsefe_grubu_dogru'] ?? 0, $data['ayt_felsefe_grubu_yanlis'] ?? 0),
        'din' => calculateNet($data['ayt_dkab_/_ilave_felsefe_dogru'] ?? 0, $data['ayt_dkab_/_ilave_felsefe_yanlis'] ?? 0),
    ];
}


function calculateYDTNet($data) {
    return calculateNet($data['ydt_dogru'] ?? 0, $data['ydt_yanlis'] ?? 0);
}

function calculateTYTScore($netScores) {
    // Define weights for each TYT subject
    $weights = [
        'turkce' => 2.9156,
        'sosyal' => 2.94461,
        'matematik' => 2.93256,
        'fen' => 3.15559,
    ];

    // Calculate the total score using the weights
    $total_score = 144.945 + (
        ($netScores['turkce'] * $weights['turkce']) +
        ($netScores['sosyal'] * $weights['sosyal']) +
        ($netScores['matematik'] * $weights['matematik']) +
        ($netScores['fen'] * $weights['fen'])
    );

    if ($total_score > 500) {
        $total_score = 500;
    }


    return $total_score;
}


function calculateAYTScores($netScores, $ydtNet) {
    // Define weights for AYT subjects
    $weights = [
        'matematik' => 3.00,      // Sayısal and Eşit Ağırlık
        'fizik' => 2.857,         // Sayısal
        'kimya' => 3.077,         // Sayısal
        'biyoloji' => 3.077,      // Sayısal
        'edebiyat' => 3.00,       // Eşit Ağırlık and Sözel
        'tarih1' => 2.803,        // Eşit Ağırlık
        'cografya1' => 3.33,      // Eşit Ağırlık
        'tarih2' => 2.91,         // Sözel
        'cografya2' => 2.91,      // Sözel
        'felsefe' => 3.00,        // Sözel
        'din' => 3.33,            // Sözel
    ];
    

    // Calculate net scores for each field using the defined weights
    $sayNet = ($netScores['matematik'] * $weights['matematik']) +
              ($netScores['fizik'] * $weights['fizik']) +
              ($netScores['kimya'] * $weights['kimya']) +
              ($netScores['biyoloji'] * $weights['biyoloji']);

    $sozNet = ($netScores['edebiyat'] * $weights['edebiyat']) +
              ($netScores['tarih1'] * $weights['tarih1']) +
              ($netScores['cografya1'] * $weights['cografya1']) +
              ($netScores['tarih2'] * $weights['tarih2']) +
              ($netScores['cografya2'] * $weights['cografya2']) +
              ($netScores['felsefe'] * $weights['felsefe']) +
              ($netScores['din'] * $weights['din']);

    $eaNet = ($netScores['matematik'] * $weights['matematik']) +
             ($netScores['edebiyat'] * $weights['edebiyat']) +
             ($netScores['tarih1'] * $weights['tarih1']) +
             ($netScores['cografya1'] * $weights['cografya1']);

    // debug_log("Net Scores:");
    // debug_log($netScores);
    // debug_log("Weights:");
    // debug_log($weights);
    // debug_log("Net Scores:");
    // debug_log($sayNet);
    // debug_log("      ---------       ");
    // debug_log($sozNet);
    // debug_log("      ---------       ");
    // debug_log($eaNet);
    // debug_log("      ---------       ");
    // debug_log($ydtNet);
    // debug_log("      ---------       ");


    return [
        'say' => 133.284 + ($sayNet * 1),  // Adjust the multiplier if needed
        'soz' => 130.358 + ($sozNet * 1),  // Adjust the multiplier if needed
        'ea' => 132.283 + ($eaNet * 1),    // Adjust the multiplier if needed
        'dil' => 110.581 + ($ydtNet * 2.61),
    ];
}

function calculateOBP($diplomaNotu, $oncekiYerlestirme) {
    $diplomaNotu = max(50, min(100, floatval($diplomaNotu)));
    $obp = $diplomaNotu * 5;
    if ($oncekiYerlestirme) {
        $obp *= 0.6;
    }
    return $obp;
}

// Use $data instead of $_POST
$results = calculateScores($data);

// Clear any output buffer
ob_end_clean();

// Ensure only the JSON result is output
echo json_encode($results);
?>
