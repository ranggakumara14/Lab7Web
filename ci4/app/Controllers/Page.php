<?php
namespace App\Controllers;
class Page extends BaseController
{
    public function about()
    {
    return view('about', [
    'title' => 'Halaman About',
    'content' => 'Ini adalah halaman about yang menjelaskan tentang isi
    halaman ini.'
    ]);
    }
    public function contact()
    {
        return view('pages/contact', [
            'title' => 'Hubungi Saya'
        ]);
    }
public function faqs()
{
echo "Ini halaman FAQ";
}
public function tos()
{
echo "ini halaman Term of Services";
}
}