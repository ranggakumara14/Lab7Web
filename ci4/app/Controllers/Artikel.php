<?php
namespace App\Controllers;
use App\Models\ArtikelModel;
use App\Models\KategoriModel;
use CodeIgniter\Controller;
class Artikel extends BaseController
{
public function index()
{
$title = 'Daftar Artikel';
$model = new ArtikelModel();
$artikel = $model->findAll();
return view('artikel/index', compact('artikel', 'title'));
}
public function admin_index()
{
    $model = new ArtikelModel();
    $q = $this->request->getVar('q') ?? '';
    $kategori_id = $this->request->getVar('kategori_id') ?? '';
    $page = $this->request->getVar('page') ?? 1;

    $builder = $model->db->table('artikel')
        ->select('artikel.*, kategori.nama_kategori')
        ->join('kategori', 'kategori.id_kategori = artikel.id_kategori');

    if ($q != '') {
        $builder->like('artikel.judul', $q);
    }

    if ($kategori_id != '') {
        $builder->where('artikel.id_kategori', $kategori_id);
    }

    $artikel = $builder->get()->getResultArray();

    $pager = ['links' => []]; // kalau belum pakai pagination AJAX, ini dummy aja

    $data = [
        'q' => $q,
        'kategori_id' => $kategori_id,
        'artikel' => $artikel,
        'pager' => $pager
    ];

    if ($this->request->isAJAX()) {
        return $this->response->setJSON($data);
    } else {
        $kategoriModel = new KategoriModel();

        $data = [
            'q' => $q,
            'kategori_id' => $kategori_id,
            'artikel' => $artikel,
            'pager' => $pager,
            'kategori' => $kategoriModel->findAll(),
            'title' => 'Daftar Artikel (Admin)',
        ];
        
        return view('artikel/admin_index', $data);
    }
}

public function add()
{
    $artikel = new ArtikelModel();
    $validation = \Config\Services::validation();

    // Set validasi form
    $validation->setRules([
        'judul' => 'required',
        'isi' => 'required',
        'id_kategori' => 'required',
    ]);

    // Jika form disubmit & valid
    if ($this->request->getMethod() === 'post' && $validation->withRequest($this->request)->run()) {
        $artikel->insert([
            'judul' => $this->request->getPost('judul'),
            'isi' => $this->request->getPost('isi'),
            'id_kategori' => $this->request->getPost('id_kategori'),
        ]);

        return redirect()->to('/admin/artikel');
    }

    // Tampilkan form
    $kategoriModel = new KategoriModel();
    $kategori = $kategoriModel->findAll();

    $title = 'Tambah Artikel';
    return view('artikel/form_add', compact('title', 'kategori', 'validation'));
}

public function edit($id)
{
$artikel = new ArtikelModel();
// validasi data.
$validation = \Config\Services::validation();
$validation->setRules(['judul' => 'required']);
$isDataValid = $validation->withRequest($this->request)->run();
if ($isDataValid)
{
$artikel->update($id, [
'judul' => $this->request->getPost('judul'),
'isi' => $this->request->getPost('isi'),
]);
return redirect('admin/artikel');
}
// ambil data lama
$data = $artikel->where('id', $id)->first();
$title = "Edit Artikel";
return view('artikel/form_edit', compact('title', 'data'));
}
public function delete($id)
{
$artikel = new ArtikelModel();
$artikel->delete($id);
return redirect('admin/artikel');
}
public function view($slug)
{
$model = new ArtikelModel();
$artikel = $model->where([
'slug' => $slug
])->first();
// Menampilkan error apabila data tidak ada.
if (!$artikel)
{
throw PageNotFoundException::forPageNotFound();
}
$title = $artikel['judul'];
return view('artikel/detail', compact('artikel', 'title'));
}
}