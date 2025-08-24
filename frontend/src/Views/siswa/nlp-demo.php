<?php
// Data for this view will be passed from the NLPDemoController
$user = $_SESSION['user_data'] ?? ['name' => 'Guest'];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-brain me-2"></i>
        Demo NLP Analysis
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <div class="dropdown d-inline-block me-2">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="exampleDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-lightbulb me-1"></i>Gunakan Contoh
                </button>
                <ul class="dropdown-menu" aria-labelledby="exampleDropdown" id="example-dropdown-menu">
                    <!-- Examples will be dynamically loaded here -->
                </ul>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-text">
                <i class="fas fa-eraser me-1"></i>Clear
            </button>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info mb-4">
    <h6><i class="fas fa-info-circle me-2"></i>Tentang NLP Analysis</h6>
    <p class="mb-2">Sistem NLP (Natural Language Processing) POINTMARKET menganalisis teks Anda berdasarkan beberapa faktor:</p>
    <div class="row">
        <div class="col-md-6">
            <ul class="mb-0">
                <li><strong>Grammar:</strong> Tata bahasa dan ejaan</li>
                <li><strong>Keywords:</strong> Kata kunci yang relevan</li>
                <li><strong>Structure:</strong> Organisasi dan alur</li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="mb-0">
                <li><strong>Readability:</strong> Keterbacaan teks</li>
                <li><strong>Sentiment:</strong> Tone positif/negatif</li>
                <li><strong>Complexity:</strong> Tingkat kompleksitas</li>
            </ul>
        </div>
    </div>
</div>

<!-- Demo Form -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-edit me-2"></i>Coba Analisis Teks Anda</h5>
    </div>
    <div class="card-body">
        <form id="nlpDemoForm">
            <div class="mb-3">
                <label for="text_to_analyze" class="form-label">Tulis teks yang ingin dianalisis:</label>
                <textarea 
                    id="text_to_analyze" 
                    name="text_to_analyze" 
                    class="form-control" 
                    rows="8" 
                    placeholder="Contoh: Teknologi dalam pendidikan sangat penting karena dapat meningkatkan kualitas pembelajaran. Dengan adanya komputer dan internet, siswa dapat mengakses berbagai sumber belajar yang tidak terbatas..."
                    data-nlp="true"
                    data-context="assignment"
                ></textarea>
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Minimal 10 karakter untuk analisis. Analisis otomatis akan berjalan 3 detik setelah berhenti mengetik.
                </div>
            </div>
            
            <!-- <div class="mb-3"> -->
            <!--     <label for="context_type" class="form-label">Konteks:</label> -->
            <!--     <select id="context_type" name="context_type" class="form-select"> -->
            <!--         <option value="assignment">Assignment (Tugas)</option> -->
            <!--         <option value="matematik">Matematika</option> -->
            <!--         <option value="fisika">Fisika</option> -->
            <!--         <option value="kimia">Kimia</option> -->
            <!--         <option value="biologi">Biologi</option> -->
            <!--     </select> -->
            <!-- </div> -->
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search me-1"></i>Analisis Teks
            </button>
        </form>
    </div>
</div>

<!-- NLP Analysis Results -->
<?php $renderer->includePartial('components/partials/vark_result', []); ?>

<!-- <!-- Debugging Tools --> -->
<!-- <div class="card mt-4"> -->
<!--     <div class="card-header"> -->
<!--         <h6><i class="fas fa-tools me-2"></i>Debugging Tools</h6> -->
<!--     </div> -->
<!--     <div class="card-body"> -->
<!--         <div class="row"> -->
<!--             <div class="col-md-4 mb-2"> -->
<!--                 <button id="test-api" class="btn btn-outline-primary btn-sm w-100"> -->
<!--                     <i class="fas fa-vial me-1"></i>Test API -->
<!--                 </button> -->
<!--             </div> -->
<!--             <div class="col-md-4 mb-2"> -->
<!--                 <button id="view-session" class="btn btn-outline-secondary btn-sm w-100"> -->
<!--                     <i class="fas fa-key me-1"></i>View Session -->
<!--                 </button> -->
<!--             </div> -->
<!--             <div class="col-md-4 mb-2"> -->
<!--                 <a href="/nlp-diagnostics" class="btn btn-outline-info btn-sm w-100"> -->
<!--                     <i class="fas fa-microscope me-1"></i>Run Diagnostics -->
<!--                 </a> -->
<!--             </div> -->
<!--         </div> -->
<!--         <div id="debug-output" class="mt-3" style="display: none;"> -->
<!--             <pre id="debug-content" class="bg-light p-3 rounded"></pre> -->
<!--         </div> -->
<!--     </div> -->
<!-- </div> -->

<!-- Examples -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6><i class="fas fa-star me-2"></i>Contoh Teks Berkualitas Tinggi</h6>
            </div>
            <div class="card-body">
                <div class="example-text">
                    <p><strong>Topik:</strong> Teknologi dalam Pendidikan</p>
                    <p>"Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran. Pertama, teknologi menyediakan akses ke sumber belajar yang tidak terbatas melalui internet. Kedua, aplikasi pembelajaran interaktif memungkinkan siswa untuk belajar dengan cara yang lebih menarik dan efektif. Ketiga, platform digital memfasilitasi komunikasi antara guru dan siswa di luar jam sekolah. Dengan demikian, integrasi teknologi dalam pendidikan bukan hanya trend, tetapi kebutuhan fundamental untuk menciptakan sistem pembelajaran yang adaptif dan berkelanjutan."</p>
                </div>
                <div class="mt-2">
                    <span class="score-badge score-high">Prediksi Score: 85-92</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Contoh Teks Perlu Perbaikan</h6>
            </div>
            <div class="card-body">
                <div class="example-text">
                    <p><strong>Topik:</strong> Teknologi dalam Pendidikan</p>
                    <p>"teknologi bagus untuk sekolah karena bisa belajar dengan komputer dan internet juga bisa cari materi di google terus bisa cari materi di google terus bisa ngerjain tugas lebih gampang pokoknya teknologi sangat membantu"</p>
                </div>
                <div class="mt-2">
                    <span class="score-badge score-low">Prediksi Score: 35-45</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const API_BASE_URL = '<?php echo rtrim(API_BASE_URL, '/'); ?>';
    const JWT_TOKEN = '<?php echo $_SESSION['jwt_token'] ?? ''; ?>';

    document.addEventListener('DOMContentLoaded', function() {
        const textInput = document.getElementById('text_to_analyze');
        // const contextSelect = document.getElementById('context_type');
        const nlpDemoForm = document.getElementById('nlpDemoForm');
        const nlpResultsContainer = document.getElementById('vark-results-container');
        const nlpResultsBody = document.getElementById('vark-results-body');
        const clearTextBtn = document.getElementById('clear-text');
        const testApiBtn = document.getElementById('test-api');
        const viewSessionBtn = document.getElementById('view-session');
        const debugOutput = document.getElementById('debug-output');
        const debugContent = document.getElementById('debug-content');
        const exampleDropdownMenu = document.getElementById('example-dropdown-menu');

        const exampleTexts = {
            'assignment': [
                {
                    name: 'Pentingnya Teknologi Pendidikan',
                    text: "Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran di era digital. Pertama, teknologi menyediakan akses ke sumber belajar yang tidak terbatas melalui internet, memungkinkan siswa untuk mengeksplorasi materi pembelajaran dari berbagai perspektif dan sumber yang kredibel. Kedua, aplikasi pembelajaran interaktif seperti simulasi, game edukasi, dan platform e-learning memungkinkan siswa untuk belajar dengan cara yang lebih menarik, efektif, dan sesuai dengan gaya belajar masing-masing. Ketiga, platform digital memfasilitasi komunikasi dan kolaborasi antara guru dan siswa di luar jam sekolah, menciptakan lingkungan belajar yang lebih fleksibel dan responsif. Keempat, teknologi memungkinkan personalisasi pembelajaran melalui sistem adaptif yang dapat menyesuaikan konten dan kecepatan belajar dengan kemampuan individual siswa. Dengan demikian, integrasi teknologi dalam pendidikan bukan hanya sebuah tren, tetapi kebutuhan fundamental untuk mempersiapkan generasi masa depan yang kompeten dan siap menghadapi tantangan global."
                },
                {
                    name: 'Dampak Media Sosial',
                    text: "Media sosial telah mengubah cara manusia berinteraksi dan berkomunikasi secara fundamental dalam beberapa dekade terakhir. Di satu sisi, media sosial memberikan dampak positif yang signifikan seperti memudahkan komunikasi jarak jauh, memperluas jaringan sosial, dan memberikan platform untuk ekspresi diri dan kreativitas. Platform seperti Facebook, Instagram, dan Twitter memungkinkan orang untuk tetap terhubung dengan keluarga dan teman, berbagi pengalaman, dan membangun komunitas berdasarkan minat yang sama. Di sisi lain, media sosial juga membawa dampak negatif yang perlu diwaspadai, seperti penyebaran informasi palsu atau hoaks, cyberbullying, kecanduan digital, dan masalah privasi data. Fenomena FOMO (Fear of Missing Out) dan tekanan untuk tampil sempurna di media sosial dapat menyebabkan masalah kesehatan mental, terutama pada remaja. Oleh karena itu, penggunaan media sosial yang bijak dan bertanggung jawab menjadi kunci untuk memaksimalkan manfaatnya sambil meminimalkan risiko yang ada."
                },
                {
                    name: 'Lingkungan Hidup',
                    text: "Pelestarian lingkungan hidup merupakan tanggung jawab bersama yang memerlukan komitmen dari semua pihak untuk memastikan keberlanjutan planet ini bagi generasi mendatang. Krisis lingkungan yang kita hadapi saat ini, seperti perubahan iklim, pencemaran udara dan air, deforestasi, dan kepunahan spesies, memerlukan tindakan nyata dan segera. Setiap individu dapat berperan dalam pelestarian lingkungan melalui tindakan sederhana namun berdampak, seperti mengurangi penggunaan plastik sekali pakai, menghemat energi listrik, menggunakan transportasi ramah lingkungan, dan menerapkan prinsip 3R (Reduce, Reuse, Recycle) dalam kehidupan sehari-hari. Pemerintah dan perusahaan juga memiliki peran krusial dalam menciptakan kebijakan dan praktik bisnis yang berkelanjutan. Pendidikan lingkungan sejak dini sangat penting untuk membentuk kesadaran dan perilaku yang peduli terhadap alam. Dengan kerja sama dan komitmen bersama, kita dapat menjaga kelestarian lingkungan dan menciptakan masa depan yang lebih hijau dan berkelanjutan."
                }
            ],
            'matematik': [
                {
                    name: 'Konsep Fungsi Linear',
                    text: "Fungsi linear merupakan salah satu konsep fundamental dalam matematika yang memiliki aplikasi luas dalam kehidupan sehari-hari dan berbagai bidang ilmu. Secara matematis, fungsi linear adalah fungsi yang dapat dinyatakan dalam bentuk f(x) = ax + b, dimana a dan b adalah konstanta dan a ≠ 0. Karakteristik utama fungsi linear adalah grafik yang berupa garis lurus dengan kemiringan konstan. Koefisien a menentukan kemiringan (slope) garis, sedangkan konstanta b menentukan titik potong dengan sumbu y (y-intercept). Fungsi linear banyak digunakan dalam pemodelan hubungan proporsional, seperti menghitung biaya produksi, konversi suhu, dan analisis ekonomi sederhana. Pemahaman yang baik tentang fungsi linear menjadi dasar untuk mempelajari konsep matematika yang lebih kompleks seperti sistem persamaan linear, program linear, dan kalkulus."
                },
                {
                    name: 'Geometri Ruang',
                    text: "Geometri ruang atau geometri tiga dimensi mempelajari sifat-sifat dan hubungan antara titik, garis, bidang, dan bangun ruang dalam ruang tiga dimensi. Konsep dasar geometri ruang meliputi koordinat tiga dimensi (x, y, z), jarak antara dua titik, persamaan bidang, dan persamaan garis dalam ruang. Bangun ruang seperti kubus, balok, prisma, limas, tabung, kerucut, dan bola memiliki rumus volume dan luas permukaan yang spesifik. Aplikasi geometri ruang sangat luas dalam kehidupan nyata, mulai dari arsitektur dan konstruksi bangunan, desain produk industri, animasi komputer, hingga navigasi GPS. Pemahaman geometri ruang juga penting dalam bidang fisika untuk memahami konsep vektor, momentum sudut, dan medan elektromagnetik. Kemampuan visualisasi spasial yang dikembangkan melalui pembelajaran geometri ruang sangat bermanfaat dalam pemecahan masalah teknis dan kreatif."
                }
            ],
            'fisika': [
                {
                    name: 'Hukum Newton',
                    text: "Hukum Newton tentang gerak merupakan fondasi mekanika klasik yang menjelaskan hubungan antara gaya dan gerak benda. Hukum pertama Newton atau hukum inersia menyatakan bahwa benda yang diam akan tetap diam dan benda yang bergerak akan tetap bergerak dengan kecepatan konstan dalam garis lurus, kecuali ada gaya eksternal yang bekerja padanya. Hukum kedua Newton menyatakan bahwa percepatan suatu benda berbanding lurus dengan gaya total yang bekerja padanya dan berbanding terbalik dengan massanya, yang dinyatakan dalam rumus F = ma. Hukum ketiga Newton menyatakan bahwa untuk setiap aksi terdapat reaksi yang sama besar tetapi berlawanan arah. Ketiga hukum ini dapat diamati dalam kehidupan sehari-hari, seperti saat kita berjalan, mengendarai kendaraan, atau melempar bola. Pemahaman hukum Newton sangat penting dalam rekayasa, astronautika, dan teknologi transportasi modern."
                },
                {
                    name: 'Energi dan Momentum',
                    text: "Konsep energi dan momentum merupakan dua besaran fundamental dalam fisika yang berperan penting dalam analisis gerak dan tumbukan. Energi adalah kemampuan untuk melakukan usaha dan dapat berubah bentuk dari satu jenis ke jenis lainnya, seperti energi kinetik, energi potensial, energi panas, dan energi listrik. Hukum kekekalan energi menyatakan bahwa energi tidak dapat diciptakan atau dimusnahkan, tetapi hanya dapat diubah dari satu bentuk ke bentuk lainnya. Momentum adalah besaran vektor yang didefinisikan sebagai perkalian antara massa dan kecepatan benda (p = mv). Hukum kekekalan momentum menyatakan bahwa momentum total sistem tertutup akan tetap konstan jika tidak ada gaya eksternal yang bekerja. Aplikasi konsep energi dan momentum dapat ditemukan dalam analisis tumbukan kendaraan, desain roller coaster, peluncuran roket, dan pembangkit listrik. Pemahaman kedua konsep ini sangat penting dalam pengembangan teknologi dan keselamatan transportasi."
                }
            ],
            'kimia': [
                {
                    name: 'Reaksi Kimia',
                    text: "Reaksi kimia adalah proses dimana satu atau lebih zat (reaktan) berubah menjadi zat lain (produk) dengan susunan atom yang berbeda. Dalam reaksi kimia, ikatan kimia antara atom-atom dalam reaktan putus dan terbentuk ikatan baru dalam produk, namun jenis dan jumlah atom tetap sama sesuai dengan hukum kekekalan massa. Reaksi kimia dapat diklasifikasikan menjadi beberapa jenis, seperti reaksi sintesis (penggabungan), reaksi dekomposisi (penguraian), reaksi substitusi (penggantian), dan reaksi pertukaran ganda. Faktor-faktor yang mempengaruhi laju reaksi kimia meliputi konsentrasi reaktan, suhu, luas permukaan, dan keberadaan katalis. Reaksi kimia terjadi di mana-mana dalam kehidupan sehari-hari, mulai dari proses metabolisme dalam tubuh, pembakaran bahan bakar, fotosintesis pada tumbuhan, hingga proses industri pembuatan berbagai produk kimia. Pemahaman reaksi kimia sangat penting dalam pengembangan obat-obatan, material baru, dan teknologi ramah lingkungan."
                },
                {
                    name: 'Struktur Atom',
                    text: "Struktur atom merupakan konsep fundamental dalam kimia yang menjelaskan susunan partikel-partikel subatomik dalam atom. Atom terdiri dari inti atom (nukleus) yang bermuatan positif dan dikelilingi oleh elektron yang bermuatan negatif. Inti atom mengandung proton yang bermuatan positif dan neutron yang tidak bermuatan. Model atom modern menggambarkan elektron sebagai awan probabilitas yang mengelilingi inti dalam orbital-orbital dengan tingkat energi tertentu. Konfigurasi elektron dalam orbital menentukan sifat kimia unsur, seperti kemampuan membentuk ikatan kimia dan reaktivitas. Tabel periodik unsur disusun berdasarkan nomor atom (jumlah proton) dan menunjukkan pola periodik sifat-sifat unsur. Pemahaman struktur atom sangat penting untuk memahami ikatan kimia, sifat material, spektroskopi, dan teknologi nuklir. Konsep ini juga menjadi dasar pengembangan teknologi modern seperti laser, transistor, dan panel surya."
                }
            ],
            'biologi': [
                {
                    name: 'Sistem Peredaran Darah',
                    text: "Sistem peredaran darah manusia merupakan sistem transportasi vital yang berfungsi mengangkut oksigen, nutrisi, hormon, dan zat-zat penting lainnya ke seluruh tubuh, serta mengangkut limbah metabolisme untuk dibuang. Sistem ini terdiri dari jantung sebagai pompa utama, pembuluh darah sebagai saluran transportasi, dan darah sebagai medium pengangkut. Jantung memiliki empat ruang yaitu dua atrium (serambi) dan dua ventrikel (bilik) yang bekerja secara terkoordinasi dalam siklus jantung. Pembuluh darah terbagi menjadi arteri yang mengangkut darah dari jantung, vena yang mengangkut darah kembali ke jantung, dan kapiler yang memfasilitasi pertukaran zat antara darah dan jaringan. Darah mengandung sel darah merah (eritrosit) yang membawa oksigen, sel darah putih (leukosit) yang berperan dalam sistem imun, keping darah (trombosit) untuk pembekuan darah, dan plasma sebagai medium cair. Gangguan pada sistem peredaran darah seperti hipertensi, aterosklerosis, dan penyakit jantung koroner dapat berdampak serius pada kesehatan."
                },
                {
                    name: 'Fotosintesis',
                    text: "Fotosintesis adalah proses biokimia fundamental yang dilakukan oleh tumbuhan, alga, dan beberapa jenis bakteri untuk mengubah energi cahaya matahari menjadi energi kimia dalam bentuk glukosa. Proses ini terjadi di kloroplas, khususnya di bagian tilakoid yang mengandung pigmen klorofil. Fotosintesis terdiri dari dua tahap utama yaitu reaksi terang (foto-reaksi) dan reaksi gelap (siklus Calvin). Dalam reaksi terang, energi cahaya diserap oleh klorofil untuk memecah molekul air (H2O) menjadi hidrogen dan oksigen, sambil menghasilkan ATP dan NADPH. Dalam reaksi gelap, CO2 dari atmosfer difiksasi menjadi glukosa menggunakan energi dari ATP dan NADPH yang dihasilkan pada reaksi terang. Fotosintesis sangat penting bagi kehidupan di Bumi karena menghasilkan oksigen yang diperlukan untuk respirasi dan menjadi dasar rantai makanan. Proses ini juga berperan dalam mengurangi kadar CO2 di atmosfer, sehingga membantu mengatasi efek rumah kaca dan perubahan iklim."
                }
            ]
        };

        // Function to populate example dropdown
        function populateExampleDropdown() {
            let html = '';
            for (const category in exampleTexts) {
                html += `<li><h6 class="dropdown-header">${category.charAt(0).toUpperCase() + category.slice(1)}</h6></li>`;
                exampleTexts[category].forEach((example, index) => {
                    html += `<li><a class="dropdown-item" href="#" data-category="${category}" data-index="${index}">${example.name}</a></li>`;
                });
                html += `<li><hr class="dropdown-divider"></li>`;
            }
            exampleDropdownMenu.innerHTML = html;

            // Add event listeners to new dropdown items
            exampleDropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const category = this.getAttribute('data-category');
                    const index = parseInt(this.getAttribute('data-index'));
                    const example = exampleTexts[category][index];
                    
                    if (example) {
                        textInput.value = example.text;
                        // contextSelect.value = category;
                        
                        // Add a small animation to show the text was loaded
                        textInput.style.backgroundColor = '#e3f2fd';
                        setTimeout(() => {
                            textInput.style.backgroundColor = '';
                        }, 500);
                    }
                });
            });
        }

        populateExampleDropdown();

        // Clear text and hide results
        clearTextBtn.addEventListener('click', function() {
            textInput.value = '';
            nlpResultsContainer.style.display = 'none';
            nlpResultsBody.innerHTML = ''; // Clear previous results
            debugOutput.style.display = 'none'; // Hide debug output
            debugContent.innerHTML = ''; // Clear debug content
        });

        // Handle form submission
        nlpDemoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const text = textInput.value.trim();
            // const contextType = contextSelect.value;
            
            if (!text) {
                alert('Please enter some text to analyze.');
                return;
            }
            
            // Show results area and loading indicator
            nlpResultsContainer.style.display = 'block';
            nlpResultsBody.innerHTML = '<div id="loading-indicator" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Menganalisis teks...</p></div>';
            
            // Scroll to results
            nlpResultsContainer.scrollIntoView({ behavior: 'smooth' });
            
            fetch(`${API_BASE_URL}/api/v1/text-analyzer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${JWT_TOKEN}`
                },
                body: JSON.stringify({
                    text: text
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderVarkResults(data.data, 'vark-results-body');
                } else {
                    nlpResultsBody.innerHTML = `<div class="alert alert-danger">Error: ${data.message || 'Unknown error'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                nlpResultsBody.innerHTML = `<div class="alert alert-danger">An error occurred: ${error.message}</div>`;
            });
        });

        // Test API button
        testApiBtn.addEventListener('click', function() {
            debugOutput.style.display = 'block';
            debugContent.textContent = 'Testing API...';
            
            fetch(`${API_BASE_URL}/api/v1/text-analyzer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${JWT_TOKEN}`
                },
                body: JSON.stringify({
                    text: "Test text for API",
                    context_type: "general"
                })
            })
            .then(response => response.json())
            .then(data => {
                debugContent.textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                debugContent.textContent = 'Error testing API: ' + error.message;
            });
        });
        
        // View session button (placeholder for now, actual session info not exposed via API)
        viewSessionBtn.addEventListener('click', function() {
            debugOutput.style.display = 'block';
            debugContent.textContent = 'Session data not directly accessible via API for security reasons. This is a placeholder.';
        });
    });
</script>
