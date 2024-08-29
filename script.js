document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('yksForm');
    const resultsDiv = document.getElementById('results');
    const hesaplaButton = document.getElementById('hesaplaButton');
    const saveExamButton = document.getElementById('saveExamButton');

    let scoreChart;
    let lastCalculatedScores;

    // Ensure saveExamButton is disabled initially
    saveExamButton.disabled = true;

    const maxQuestions = {
        tyt: {
            turkce: 40,
            sosyal_b: 20,
            t_matematik: 40,
            fen_b: 20
        },
        ayt: {
            turk_dili_ve_edebiyati: 24,
            tarih_1: 10,
            cografya_1: 6,
            tarih_2: 11,
            cografya_2: 11,
            felsefe_grubu: 12,
            din_kulturu_ve_ahlak_bilgisi: 6,
            matematik: 40,
            fizik: 14,
            kimya: 13,
            biyoloji: 13
        },
        ydt: {
            yabanci_dil: 80
        }
    };

    function getMaxQuestionsForLesson(dersId) {
        switch(dersId) {
            // TYT
            case 'tyt_turkce': return maxQuestions.tyt.turkce;
            case 'tyt_sosyal_b.': return maxQuestions.tyt.sosyal_b;
            case 'tyt_t_matematik': return maxQuestions.tyt.t_matematik;
            case 'tyt_fen_b.': return maxQuestions.tyt.fen_b;
            
            // AYT
            case 'ayt_turk_dili_ve_edebiyati': return maxQuestions.ayt.turk_dili_ve_edebiyati;
            case 'ayt_tarih-1': return maxQuestions.ayt.tarih_1;
            case 'ayt_cografya-1': return maxQuestions.ayt.cografya_1;
            case 'ayt_tarih-2': return maxQuestions.ayt.tarih_2;
            case 'ayt_cografya-2': return maxQuestions.ayt.cografya_2;
            case 'ayt_felsefe_grubu': return maxQuestions.ayt.felsefe_grubu;
            case 'ayt_din_kulturu_ve_ahlak_bilgisi': return maxQuestions.ayt.din_kulturu_ve_ahlak_bilgisi;
            case 'ayt_matematik': return maxQuestions.ayt.matematik;
            case 'ayt_fizik': return maxQuestions.ayt.fizik;
            case 'ayt_kimya': return maxQuestions.ayt.kimya;
            case 'ayt_biyoloji': return maxQuestions.ayt.biyoloji;
            
            // YDT
            case 'ydt': return maxQuestions.ydt.yabanci_dil;
            
            default:
                console.warn(`Unexpected dersId: ${dersId}. Defaulting to 40 questions.`);
                return 40; // Varsayılan değer
        }
    }

    function updateInputs(changedInput, otherInput, maxQuestions) {
        console.log('Updating inputs:', changedInput.name, otherInput.name, 'Max:', maxQuestions);
        let changedValue = parseInt(changedInput.value) || 0;
        let otherValue = parseInt(otherInput.value) || 0;

        changedValue = Math.max(0, Math.min(changedValue, maxQuestions));
        changedInput.value = changedValue;

        if (changedValue + otherValue > maxQuestions) {
            otherValue = Math.max(0, maxQuestions - changedValue);
            otherInput.value = otherValue;
        }

        const dersId = changedInput.getAttribute('data-ders');
        updateNet(dersId);
    }

    function updateNet(dersId) {
        const dogruInput = document.querySelector(`input[name="${dersId}_dogru"]`);
        const yanlisInput = document.querySelector(`input[name="${dersId}_yanlis"]`);
        const netDisplay = document.getElementById(`${dersId}_net`);

        if (dogruInput && yanlisInput && netDisplay) {
            const dogru = parseInt(dogruInput.value) || 0;
            const yanlis = parseInt(yanlisInput.value) || 0;
            const net = dogru - (yanlis * 0.25);
            netDisplay.textContent = `Net: ${net.toFixed(2)}`;
            console.log('Updated net for', dersId, ':', net.toFixed(2));
        }
    }

    form.addEventListener('input', function(event) {
        if (event.target.classList.contains('dogru-input') || event.target.classList.contains('yanlis-input')) {
            const dersId = event.target.getAttribute('data-ders');
            console.log('Input changed:', dersId);
            const isDogruInput = event.target.classList.contains('dogru-input');
            const dogruInput = document.querySelector(`input[name="${dersId}_dogru"]`);
            const yanlisInput = document.querySelector(`input[name="${dersId}_yanlis"]`);

            let maxQuestionsForLesson = getMaxQuestionsForLesson(dersId);
            console.log('Max questions for', dersId, ':', maxQuestionsForLesson);

            updateInputs(
                isDogruInput ? dogruInput : yanlisInput,
                isDogruInput ? yanlisInput : dogruInput,
                maxQuestionsForLesson
            );
        }
    });

    hesaplaButton.addEventListener('click', function() {
        const formData = new FormData(form);
        
        // Convert FormData to a plain object
        const formDataObject = {};
        formData.forEach((value, key) => {
            formDataObject[key] = value;
        });

        fetch('calculate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formDataObject)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                alert('Hesaplama sırasında bir hata oluştu: ' + data.error);
            } else {
                lastCalculatedScores = data;
                displayResults(data);

                // Enable saveExamButton
                saveExamButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
        });
    });

    function displayResults(data) {
        const resultsDiv = document.getElementById('results');
        if (resultsDiv) {
            resultsDiv.style.display = 'block';
        }

        updateScoreTable(data);
        updateChart(data);

        // Show save exam form
        document.getElementById('saveExamForm').style.display = 'block';
    }

    function validateScore(score, maxQuestions) {
        return {
            dogru: Math.max(0, Math.min(maxQuestions, parseInt(score.dogru) || 0)),
            yanlis: Math.max(0, Math.min(maxQuestions, parseInt(score.yanlis) || 0))
        };
    }

    const saveExamModal = new bootstrap.Modal(document.getElementById('saveExamModal'));
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));

    saveExamButton.addEventListener('click', function() {
        const examName = document.getElementById('examName').value;
        const examDate = document.getElementById('examDate').value;

        if (!examName || !examDate) {
            showAlert('Lütfen tüm alanları doldurun.', 'error');
            return;
        }

        const jsonData = collectFormData();
        jsonData.examName = examName;
        jsonData.examDate = examDate;

        saveExam(jsonData);
    });

    function collectFormData() {
        const form = document.getElementById('yksForm');
        const formData = new FormData(form);
        const jsonData = {};

        formData.forEach((value, key) => {
            let keys = key.split('_');
            let nested = jsonData;

            for (let i = 0; i < keys.length - 1; i++) {
                nested[keys[i]] = nested[keys[i]] || {};
                nested = nested[keys[i]];
            }
            nested[keys[keys.length - 1]] = value;
        });

        return jsonData;
    }

    function saveExam(jsonData) {
        fetch('save_exam.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                saveExamModal.hide();
                successModal.show();
                resetForm();
            } else {
                showAlert('Hata: ' + (data.message || 'Bilinmeyen bir hata oluştu.'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Bir hata oluştu. Lütfen daha sonra tekrar deneyin.', 'error');
        });
    }

    function resetForm() {
        // Form alanlarını temizle
        document.getElementById('yksForm').reset();
    
        // Net skorları sıfırla
        document.querySelectorAll('.net-display').forEach(element => {
            element.textContent = 'Net: 0';
        });
    
        // Sonuç bölümünü gizle
        const resultsDiv = document.getElementById('results');
        if (resultsDiv) {
            resultsDiv.style.display = 'none';
        }
    
        // Grafik varsa temizle
        if (scoreChart) {
            scoreChart.destroy();
            scoreChart = null;
        }
    
        // Sonuç tablosunu sıfırla
        const table = document.getElementById('scoreTable');
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.cells[1].textContent = '-';
                row.cells[2].textContent = '-';
            });
        }
    
        // SaveExamForm'u gizle
        document.getElementById('saveExamForm').style.display = 'none';
    
        // Hesapla butonunu etkinleştir, kaydet butonunu devre dışı bırak
        hesaplaButton.disabled = false;
        saveExamButton.disabled = true;
    
        // Bugünün tarihini tekrar ayarla
        document.getElementById('examDate').valueAsDate = new Date();
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.container').insertAdjacentElement('afterbegin', alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Set today's date as default for the exam date input
    document.getElementById('examDate').valueAsDate = new Date();

    function updateScoreTable(data) {
        console.log(data);
        const table = document.getElementById('scoreTable');
        if (!table) return;
    
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const puanType = row.cells[0].textContent.replace("İ","i").replace("Ö", "o").trim().toLowerCase();
            console.log(puanType);
            let hamPuan = '-';
            let yerPuan = '-';
    
            if (data[puanType]) {
                hamPuan = data[puanType].ham !== undefined ? data[puanType].ham.toFixed(3) : '-';
                yerPuan = data[puanType].yer !== undefined ? data[puanType].yer.toFixed(3) : '-';
            }
    
            row.cells[1].textContent = hamPuan;
            row.cells[2].textContent = yerPuan;
        });
    }

    function updateChart(data) {
        const ctx = document.getElementById('scoreChart').getContext('2d');
    
        const chartData = {
            labels: ['TYT', 'SAY', 'EA', 'SÖZ', 'DİL'],
            datasets: [
                {
                    label: 'Ham Puan',
                    data: [data.tyt.ham, data.say.ham, data.ea.ham, data.soz?.ham || 0, data.dil?.ham || 0],
                    backgroundColor: '#009596',
                    borderColor: '#005F60',
                    borderWidth: 1,
                    borderRadius: 10,
                    barThickness: 25,
                },
                {
                    label: 'Yerleştirme Puanı',
                    data: [data.tyt.yer, data.say.yer, data.ea.yer, data.soz?.yer || 0, data.dil?.yer || 0],
                    backgroundColor: '#5752D1',
                    borderColor: '#2A265F',
                    borderWidth: 1,
                    borderRadius: 10,
                    barThickness: 25,
                }
            ]
        };
    
        const chartOptions = {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#343a40',
                        font: {
                            size: 16,
                            family: 'Arial, sans-serif'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: 'rgba(255, 255, 255, 0.3)',
                    borderWidth: 1,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: '#343a40',
                        font: {
                            size: 14,
                            family: 'Arial, sans-serif'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#343a40',
                        font: {
                            size: 14,
                            family: 'Arial, sans-serif'
                        }
                    }
                }
            }
        };
    
        if (scoreChart) {
            scoreChart.destroy();
        }
    
        scoreChart = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: chartOptions
        });
    }
});