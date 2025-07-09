<?= $this->include('template/admin_header'); ?>

<h2><?= $title; ?></h2>

<div class="row mb-3">
    <div class="col-md-6">
        <form id="search-form" class="form-inline">
            <input type="text" name="q" id="search-box" value="<?= $q; ?>" placeholder="Cari judul artikel" class="form-control mr-2">
            <select name="kategori_id" id="category-filter" class="form-control mr-2">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori as $k): ?>
                    <option value="<?= $k['id_kategori']; ?>" <?= ($kategori_id == $k['id_kategori']) ? 'selected' : ''; ?>>
                        <?= $k['nama_kategori']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Cari" class="btn btn-primary">
        </form>
    </div>
</div>

<!-- Tempat menampilkan hasil data -->
<div id="article-container"></div>
<div id="pagination-container"></div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    const articleContainer = $('#article-container');
    const paginationContainer = $('#pagination-container');
    const searchForm = $('#search-form');
    const searchBox = $('#search-box');
    const categoryFilter = $('#category-filter');

    // Fungsi untuk ambil data artikel
    function fetchData(url) {
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                renderArticles(response.artikel);
                renderPagination(response.pager, response.q, response.kategori_id);
            },
            error: function() {
                articleContainer.html('<p>Gagal memuat data.</p>');
                paginationContainer.empty();
            }
        });
    }

    // Render tabel artikel
    function renderArticles(articles) {
    let html = `
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul & Isi</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
    `;
    if (articles.length > 0) {
        articles.forEach(article => {
            const isiBersih = article.isi.replace(/(<([^>]+)>)/gi, "").substring(0, 80); // Hapus HTML
            const status = article.status == 1 ? 'Publish' : 'Draft';

            html += `
                <tr>
                    <td>${article.id}</td>
                    <td>
                        <strong style="display:block;">${article.judul}</strong>
                        <small>${isiBersih}...</small>
                    </td>
                    <td>${article.nama_kategori}</td>
                    <td>${status}</td>
                    <td style="white-space: nowrap;">
                        <a class="btn btn-sm btn-info mr-1" href="/admin/artikel/edit/${article.id}">Ubah</a>
                        <a class="btn btn-sm btn-danger" onclick="return confirm('Yakin menghapus data?');" href="/admin/artikel/delete/${article.id}">Hapus</a>
                    </td>
                </tr>
            `;
        });
    } else {
        html += '<tr><td colspan="5">Tidak ada data.</td></tr>';
    }

    html += '</tbody></table>';
    articleContainer.html(html);
}

    // Render pagination
    function renderPagination(pager, q, kategori_id) {
        let html = '<nav><ul class="pagination">';
        pager.links.forEach(link => {
            const url = link.url ? `${link.url}&q=${encodeURIComponent(q)}&kategori_id=${encodeURIComponent(kategori_id)}` : '#';
            html += `
                <li class="page-item ${link.active ? 'active' : ''}">
                    <a class="page-link" href="${url}">${link.title}</a>
                </li>
            `;
        });
        html += '</ul></nav>';
        paginationContainer.html(html);
    }

    // Search form submit
    searchForm.on('submit', function(e) {
        e.preventDefault();
        const q = searchBox.val();
        const kategori_id = categoryFilter.val();
        fetchData(`/admin/artikel?q=${encodeURIComponent(q)}&kategori_id=${encodeURIComponent(kategori_id)}`);
    });

    // Trigger search saat kategori berubah
    categoryFilter.on('change', function() {
        searchForm.trigger('submit');
    });

    // Pagination click handler (delegated)
    paginationContainer.on('click', '.page-link', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url !== '#') {
            fetchData(url);
        }
    });

    // Load awal
    fetchData('/admin/artikel');
});
</script>

<?= $this->include('template/admin_footer'); ?>