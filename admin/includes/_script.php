<script>
    // Canlı saat ve tarih fonksiyonu
    function updateTime() {
        const now = new Date();

        // Tarih ve saat formatları
        const dateOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'long'
        };
        const formattedDate = now.toLocaleDateString('tr-TR', dateOptions);

        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const formattedTime = now.toLocaleTimeString('tr-TR', timeOptions);

        // HTML elemanını seç ve içeriğini güncelle
        document.querySelector('#live-clock span').innerHTML = `${formattedDate} | ${formattedTime}`;
    }

    // Her saniye fonksiyonu çalıştır
    setInterval(updateTime, 1000);

    // Sayfa yüklendiğinde bir kez çalıştır
    updateTime();
</script>



<script>
    window.onload = function () {
        const entryVisibleInput = document.getElementById('entry_datetime_visible');
        const entryHiddenInput = document.getElementById('entry_datetime');

        // Tarih ve saati otomatik doldurma fonksiyonu
        function updateDateTime() {
            const now = new Date();

            // Veri tabanı için format (YYYY-MM-DD HH:MM:SS)
            const dbDate = now.toISOString().slice(0, 10);
            const dbTime = now.toTimeString().slice(0, 8);
            const dbFormat = `${dbDate} ${dbTime}`;

            // Kullanıcı için okunabilir format (Örn: "24 Eylül 2025, 14:30")
            const userOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const userFormat = now.toLocaleDateString('tr-TR', userOptions);

            if (entryVisibleInput && entryHiddenInput) {
                entryVisibleInput.value = userFormat;
                entryHiddenInput.value = dbFormat;
            }
        }

        // Sayfa yüklendiğinde hemen çalıştır
        updateDateTime();

        // Her saniye güncelleme yap
        setInterval(updateDateTime, 1000);

    };
</script>
<script>
    // Plaka alanını otomatik büyük harfe çevirme
    const plateInput = document.getElementById('plate');
    // Marka alanını otomatik büyük harfe çevirme 
    const brandInput = document.getElementById('brand');

    // Input alanına her klavye girişi yapıldığında bu fonksiyonu çalıştır
    plateInput.addEventListener('input', function (event) {
        // Girilen değeri al ve büyük harfe çevir
        event.target.value = event.target.value.toUpperCase();
    });

    brandInput.addEventListener('input', function (event) {
        // Girilen değeri al ve büyük harfe çevir
        event.target.value = event.target.value.toUpperCase();
    });
</script>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('dailyOccupancyChart').getContext('2d');
    const dailyOccupancyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                <?php for ($h = 0; $h < 24; $h++) {
                    echo "'$h:00',";
                } ?>
            ],
            datasets: [{
                label: 'Araç Sayısı',
                data: [<?= implode(',', $dolulukSaatlik) ?>],
                borderColor: 'rgba(255, 87, 34, 1)',
                backgroundColor: 'rgba(255, 87, 34, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    stepSize: 1
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
</script>