@extends('errors.layout')

@section('kode', '403')
@section('judul', 'Akses ditolak')
@section('pesan', $exception->getMessage() ?: 'Kamu tidak punya akses ke halaman ini.')
