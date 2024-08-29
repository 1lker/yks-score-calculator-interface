<?php

function turkceKarakterleriDonustur($string) {
    $bul = array('ü', 'Ü', 'ş', 'Ş', 'ç', 'Ç', 'ö', 'Ö', 'ğ', 'Ğ', 'ı', 'İ');
    $degistir = array('u', 'u', 's', 's', 'c', 'c', 'o', 'o', 'g', 'g', 'i', 'i');
    return strtolower(str_replace($bul, $degistir, $string));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YKS Puan Hesaplama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    
    <div class="container mt-5">
    <div class="container-fluid py-5">
    <div class="row justify-content-center g-4">
        <div class="col-md-6 col-lg-3">
            <a href="index.php" class="text-decoration-none">
                <div class="ultra-modern-card" data-tilt>
                    <div class="card-content">
                        <div class="icon-container">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <h5 class="card-title">Hesapla</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="my_exams.php" class="text-decoration-none">
                <div class="ultra-modern-card" data-tilt>
                    <div class="card-content">
                        <div class="icon-container">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <h5 class="card-title">Sınavlarım</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="target.php" class="text-decoration-none">
                <div class="ultra-modern-card" data-tilt>
                    <div class="card-content">
                        <div class="icon-container">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <h5 class="card-title">Hedefim</h5>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="statistics.php" class="text-decoration-none">
                <div class="ultra-modern-card" data-tilt>
                    <div class="card-content">
                        <div class="icon-container">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h5 class="card-title">İstatistiklerim</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
        <h1 class="text-center mb-5 mt-4 text-dark">YKS Puan Hesaplama</h1>
        <form id="yksForm">
            <!-- Genel Bilgiler -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Genel Bilgiler</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="diploma_notu" class="form-label">Diploma Notu</label>
                            <input type="number" class="form-control" id="diploma_notu" name="diploma_notu" min="50" max="100" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="onceki_yerlestirme" name="onceki_yerlestirme">
                                <label class="form-check-label" for="onceki_yerlestirme">
                                    Önceki sene yerleştim
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TYT -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">TYT</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $tytDersler = ['Türkçe', 'Sosyal B.', 'T. Matematik', 'Fen B.'];
                        foreach ($tytDersler as $ders) {
                            $dersId = turkceKarakterleriDonustur(str_replace(' ', '_', $ders));
                            echo '<div class="col-md-6 col-lg-3 mb-3">
                                <h6>' . $ders . '</h6>
                                <div class="input-group">
                                    <input type="number" class="form-control dogru-input" name="tyt_' . $dersId . '_dogru" placeholder="Doğru" min="0" max="40" data-ders="tyt_' . $dersId . '">
                                    <input type="number" class="form-control yanlis-input" name="tyt_' . $dersId . '_yanlis" placeholder="Yanlış" min="0" max="40" data-ders="tyt_' . $dersId . '">
                                </div>
                                <small class="form-text text-muted net-display" id="tyt_' . $dersId . '_net">Net: 0</small>
                            </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- AYT -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">AYT</h5>
                </div>
                <div class="card-body">
                    <!-- Sözel Bölüm -->
                    <div class="ayt-section">
                        <h5>Sözel</h5>
                        <div class="row">
                            <?php
                            $sozelDersler = [
                                'Türk Dili ve Edebiyatı' => 24,
                                'Tarih-1' => 10,
                                'Coğrafya-1' => 6
                            ];
                            foreach ($sozelDersler as $ders => $maxSoru) {
                                $dersId = turkceKarakterleriDonustur(str_replace(' ', '_', $ders));
                                echo '<div class="col-md-6 col-lg-4 mb-3">
                                    <label for="ayt_' . $dersId . '">' . $ders . '</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control dogru-input" name="ayt_' . $dersId . '_dogru" placeholder="Doğru" min="0" max="' . $maxSoru . '" data-ders="ayt_' . $dersId . '">
                                        <input type="number" class="form-control yanlis-input" name="ayt_' . $dersId . '_yanlis" placeholder="Yanlış" min="0" max="' . $maxSoru . '" data-ders="ayt_' . $dersId . '">
                                    </div>
                                    <small class="form-text text-muted net-display" id="ayt_' . $dersId . '_net">Net: 0</small>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Sosyal Bilimler Bölümü -->
                    <div class="ayt-section">
                        <h5>Sosyal Bilimler</h5>
                        <div class="row">
                            <?php
                            $sosyalDersler = [
                                'Tarih-2' => 11,
                                'Coğrafya-2' => 11,
                                'Felsefe Grubu' => 12,
                                'Din Kültürü ve Ahlak Bilgisi' => 6
                            ];
                            foreach ($sosyalDersler as $ders => $maxSoru) {
                                $dersId = turkceKarakterleriDonustur(str_replace(' ', '_', $ders));
                                echo '<div class="col-md-6 col-lg-3 mb-3">
                                    <label for="ayt_' . $dersId . '">' . $ders . '</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control dogru-input" name="ayt_' . $dersId . '_dogru" placeholder="Doğru" min="0" max="' . $maxSoru . '" data-ders="ayt_' . $dersId . '">
                                        <input type="number" class="form-control yanlis-input" name="ayt_' . $dersId . '_yanlis" placeholder="Yanlış" min="0" max="' . $maxSoru . '" data-ders="ayt_' . $dersId . '">
                                    </div>
                                    <small class="form-text text-muted net-display" id="ayt_' . $dersId . '_net">Net: 0</small>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Matematik-Fen Bölümü -->
                    <div class="ayt-section">
                        <h5>Matematik ve Fen Bilimleri</h5>
                        <div class="row">
                            <?php
                            $fenDersler = [
                                'Matematik' => 40,
                                'Fizik' => 14,
                                'Kimya' => 13,
                                'Biyoloji' => 13
                            ];
                            foreach ($fenDersler as $ders => $maxSoru) {
                                $dersId = turkceKarakterleriDonustur(str_replace(' ', '_', $ders));
                                echo '<div class="col-md-6 col-lg-3 mb-3">
                                    <label for="ayt_' . $dersId . '">' . $ders . '</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control dogru-input" name="ayt_' . $dersId . '_dogru" placeholder="Doğru" min="0" max="' . $maxSoru . '" data-ders="ayt_' . $dersId . '">
                                        <input type="number" class="form-control yanlis-input" name="ayt_' . $dersId . '_yanlis" placeholder="Yanlış" min="0" max="' . $maxSoru . '" data-ders="ayt_' . $dersId . '">
                                    </div>
                                    <small class="form-text text-muted net-display" id="ayt_' . $dersId . '_net">Net: 0</small>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- YDT -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">YDT</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-3">
                            <h6>Yabancı Dil Testi</h6>
                            <div class="input-group">
                                <input type="number" class="form-control dogru-input" name="ydt_dogru" placeholder="Doğru" min="0" max="80" data-ders="ydt">
                                <input type="number" class="form-control yanlis-input" name="ydt_yanlis" placeholder="Yanlış" min="0" max="80" data-ders="ydt">
                            </div>
                            <small class="form-text text-muted net-display" id="ydt_net">Net: 0</small>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" id="hesaplaButton" class="btn btn-primary btn-lg w-100">Hesapla</button>
        </form>

        <div id="results" class="row p-2 mt-5 grid">
    <h2 class="text-center mb-4 text-dark">Sonuçlar</h2>
    <div class="table-responsive glassmorphic-table">
        <table id="scoreTable" class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Puan Türü</th>
                    <th>Ham Puan</th>
                    <th>Yerleştirme Puanı</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>TYT</td><td>-</td><td>-</td></tr>
                <tr><td>SAY</td><td>-</td><td>-</td></tr>
                <tr><td>EA</td><td>-</td><td>-</td></tr>
                <tr><td>SÖZ</td><td>-</td><td>-</td></tr>
                <tr><td>DİL</td><td>-</td><td>-</td></tr>
            </tbody>
        </table>
    </div>
    <div class="mt-4 glassmorphic-chart">
        <canvas id="scoreChart"></canvas>
    </div>
   <!-- Save Exam Button -->
   <button type="button" class="btn btn-modern mt-3 mb-5" data-bs-toggle="modal" data-bs-target="#saveExamModal">
        <i class="bi bi-save me-2"></i> Sınavı Kaydet
    </button>

    <style>
    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        background-color: #28a745;
        background-image: linear-gradient(45deg, #28a745, #34ce57);
        border: none;
        border-radius: 50px;
        color: white;
        font-size: 1.2rem;
        font-weight: 500;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .btn-modern:hover {
        background-position: right center;
        background-size: 200%;
    }

    .btn-modern:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.5);
    }

    .btn-modern i {
        font-size: 1.4rem;
    }

    @media (max-width: 576px) {
        .btn-modern {
            width: 100%;
            padding: 12px 0;
            font-size: 1rem;
        }
    }
</style>

</div>

<!-- Save Exam Modal -->
<div class="modal fade" id="saveExamModal" tabindex="-1" aria-labelledby="saveExamModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="saveExamModalLabel">Sınavı Kaydet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="saveExamForm">
          <div class="mb-3">
            <label for="examName" class="form-label">Sınav Adı</label>
            <input type="text" class="form-control" id="examName" required>
          </div>
          <div class="mb-3">
            <label for="examDate" class="form-label">Sınav Tarihi</label>
            <input type="date" class="form-control" id="examDate" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="button" class="btn btn-primary" id="saveExamButton">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<!-- Save Confirmation Modal -->
<div class="modal fade" id="saveConfirmationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kayıt Durumu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="saveConfirmationMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tamam</button>
      </div>
    </div>
  </div>
</div>

<!-- Başarı Modalı -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="successModalLabel">Başarılı</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Sınavınız başarılı bir biçimde kaydedilmiştir.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
<script>
    VanillaTilt.init(document.querySelectorAll(".ultra-modern-card"), {
        max: 5,
        speed: 400,
        glare: true,
        "max-glare": 0.2,
    });
</script>
</body>
</html>
