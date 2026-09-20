<?php
require_once "../config/database.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .settings-layout {
            min-height: 100vh;
            max-width: 1440px;
        }

        .settings-main {
            flex: 1;
            background: rgba(8, 14, 22, 0.92);
            border: 1px solid rgba(148, 163, 184, 0.14);
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(1, 6, 14, 0.32);
            overflow: hidden;
        }

        .settings-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 86px;
            padding: 0 22px 0 24px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
            background: rgba(9, 15, 22, 0.82);
        }

        .settings-menu-toggle {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: #dfeafc;
            background: transparent;
            border: 1px solid transparent;
            font-size: 22px;
            cursor: pointer;
        }

        .settings-page-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #edf6ff;
        }

        .settings-date {
            color: #8fa4bb;
            font-size: 13px;
            padding-right: 2px;
        }

        .settings-shell {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            min-height: calc(100vh - 86px);
        }

        .settings-side {
            padding: 18px 18px 0;
            border-right: 1px solid rgba(148, 163, 184, 0.14);
            background: rgba(9, 15, 22, 0.28);
        }

        .settings-side h3 {
            margin: 0;
            font-size: 22px;
            color: #edf6ff;
        }

        .settings-side p {
            margin: 10px 0 18px;
            color: #8fa4bb;
            font-size: 14px;
            line-height: 1.5;
        }

        .settings-tabs {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .settings-tab {
            width: 100%;
            text-align: left;
            padding: 12px 14px;
            border: 1px solid transparent;
            border-radius: 10px;
            background: transparent;
            color: #dfeafc;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .settings-tab:hover {
            background: rgba(148, 163, 184, 0.04);
        }

        .settings-tab.active {
            background: rgba(77, 181, 255, 0.16);
            border-color: rgba(77, 181, 255, 0.2);
            color: #f3f7ff;
            box-shadow: inset 0 0 0 1px rgba(77, 181, 255, 0.12);
        }

        .settings-body {
            padding: 28px 28px 32px;
            background: rgba(8, 14, 22, 0.6);
        }

        .settings-panel {
            display: none;
        }

        .settings-panel.active {
            display: block;
        }

        .settings-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .settings-panel-header h2 {
            margin: 0;
            font-size: 32px;
            color: #edf6ff;
        }

        .settings-panel-header p {
            margin: 8px 0 0;
            color: #8fa4bb;
            font-size: 14px;
        }

        .settings-card {
            background: rgba(13, 21, 31, 0.82);
            border: 1px solid rgba(148, 163, 184, 0.14);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.02);
        }

        .settings-card > .card-title {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            color: #edf6ff;
            font-size: 16px;
            font-weight: 700;
        }

        .settings-card-body {
            padding: 20px;
        }

        .settings-profile-grid {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .settings-profile-summary {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .settings-profile-summary .mini-title {
            color: #8fa4bb;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(77, 181, 255, 0.18), rgba(42, 215, 165, 0.12));
            border: 1px solid rgba(77, 181, 255, 0.2);
            display: grid;
            place-items: center;
            font-size: 32px;
            color: #8ad4ff;
        }

        .profile-upload-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(148, 163, 184, 0.03);
            color: #dfeafc;
            font-size: 12px;
            cursor: pointer;
        }

        .settings-form {
            display: grid;
            gap: 18px;
        }

        .field-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field label {
            color: #dfeafc;
            font-size: 13px;
            font-weight: 600;
        }

        .field input,
        .field textarea {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: rgba(10, 15, 22, 0.9);
            color: #edf6ff;
            border-radius: 12px;
            min-height: 48px;
            padding: 12px 14px;
            font-size: 15px;
            outline: none;
        }

        .field textarea {
            min-height: 110px;
            resize: vertical;
        }

        .primary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 22px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #2b6dfa, #4db5ff);
            color: #ffffff;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            margin-left: auto;
            margin-top: 8px;
            width: 220px;
        }

        .users-panel {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .panel-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .panel-title {
            color: #edf6ff;
            font-size: 15px;
            font-weight: 700;
        }

        .add-btn {
            border: 1px solid rgba(77, 181, 255, 0.32);
            background: rgba(77, 181, 255, 0.1);
            color: #dff2ff;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .user-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .user-row {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 14px;
            min-height: 72px;
            padding: 14px 16px;
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 12px;
            background: rgba(12, 18, 26, 0.72);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #8b5cf6, #5b8cff);
        }

        .user-name {
            font-size: 16px;
            color: #edf6ff;
            margin-bottom: 2px;
        }

        .user-role {
            color: #8fa4bb;
            font-size: 12px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 82px;
            height: 28px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: #dff4e4;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .role-badge.admin {
            background: rgba(139, 92, 246, 0.12);
            border-color: rgba(139, 92, 246, 0.2);
            color: #e1d2ff;
        }

        .role-badge.viewer {
            background: rgba(34, 197, 94, 0.12);
            border-color: rgba(34, 197, 94, 0.2);
            color: #dffce9;
        }

        .category-list,
        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .category-row,
        .notification-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
            min-height: 58px;
            padding: 12px 16px;
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 12px;
            background: rgba(12, 18, 26, 0.72);
        }

        .category-row-left,
        .notification-row-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .category-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .category-name {
            color: #edf6ff;
            font-size: 15px;
            font-weight: 600;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid transparent;
            color: #dffce9;
        }

        .tag.pemasukan {
            background: rgba(16, 185, 129, 0.12);
            border-color: rgba(16, 185, 129, 0.2);
            color: #8ef2c5;
        }

        .tag.pengeluaran {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.2);
            color: #ffb2b2;
        }

        .row-action {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: rgba(148, 163, 184, 0.03);
            color: #dfeafc;
            display: grid;
            place-items: center;
            cursor: pointer;
            font-size: 16px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 42px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            inset: 0;
            background: rgba(148, 163, 184, 0.22);
            border-radius: 999px;
            transition: 0.2s ease;
            cursor: pointer;
        }

        .slider::before {
            content: "";
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 3px;
            border-radius: 50%;
            background: #f4f8ff;
            transition: 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }

        .switch input:checked + .slider {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
        }

        .switch input:checked + .slider::before {
            transform: translateX(18px);
        }

        .notification-copy {
            color: #8fa4bb;
            display: block;
            margin-top: 2px;
            font-size: 12px;
        }

        @media (max-width: 980px) {
            .settings-shell {
                grid-template-columns: 1fr;
            }

            .settings-side {
                border-right: none;
                border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            }

            .settings-profile-grid,
            .field-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="app-shell settings-layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">M</div>
            <div class="brand-info">
                <strong>Masjid Al-Ikhlas</strong>
                <span>Manajemen Keuangan</span>
            </div>
        </div>

        <nav class="nav">
            <a href="dashboard.php" class="nav-item">
                <span class="nav-icon">◫</span>
                <span>Dashboard</span>
            </a>
            <a href="pemasukan.php" class="nav-item">
                <span class="nav-icon">↗</span>
                <span>Pemasukan</span>
            </a>
            <a href="pengeluaran.php" class="nav-item">
                <span class="nav-icon">↘</span>
                <span>Pengeluaran</span>
            </a>
            <a href="laporan.php" class="nav-item">
                <span class="nav-icon">◌</span>
                <span>Laporan</span>
            </a>
            <a href="pengaturan.php" class="nav-item active">
                <span class="nav-icon">⚑</span>
                <span>Pengaturan</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="avatar">AD</div>
            <div>
                <strong>Admin</strong>
                <span>Bandarh</span>
            </div>
        </div>
    </aside>

    <main class="settings-main">
        <header class="settings-topbar">
            <button type="button" class="settings-menu-toggle" aria-label="Menu">☰</button>
            <div class="settings-date">Masjid Al-Ikhlas — September 2026</div>
        </header>

        <div class="settings-shell">
            <aside class="settings-side">
                <h3>Pengaturan</h3>
                <p>Kelola profil dan preferensi sistem</p>

                <div class="settings-tabs" role="tablist" aria-label="Pengaturan">
                    <button type="button" class="settings-tab active" data-tab="profile">Profil Masjid</button>
                    <button type="button" class="settings-tab" data-tab="users">Pengguna</button>
                    <button type="button" class="settings-tab" data-tab="categories">Kategori</button>
                    <button type="button" class="settings-tab" data-tab="notifications">Notifikasi</button>
                </div>
            </aside>

            <div class="settings-body">
                <section class="settings-panel active" data-panel="profile">
                    <div class="settings-panel-header">
                        <div>
                            <h2>Pengaturan</h2>
                            <p>Kelola profil dan preferensi sistem</p>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-title">Profil Masjid</div>
                        <div class="settings-card-body">
                            <div class="settings-profile-grid">
                                <div class="settings-profile-summary">
                                    <div>
                                        <div class="mini-title">Profil Masjid</div>
                                        <div class="profile-avatar">🏛</div>
                                    </div>
                                    <button type="button" class="profile-upload-btn">Ganti foto</button>
                                </div>

                                <form class="settings-form">
                                    <div class="field-row">
                                        <div class="field">
                                            <label for="nama-masjid">Nama Masjid</label>
                                            <input id="nama-masjid" type="text" value="Masjid Al-Ikhlas">
                                        </div>
                                        <div class="field">
                                            <label for="tahun-berdiri">Tahun Berdiri</label>
                                            <input id="tahun-berdiri" type="text" value="1995">
                                        </div>
                                    </div>

                                    <div class="field-row">
                                        <div class="field">
                                            <label for="telepon">Telepon</label>
                                            <input id="telepon" type="text" value="021-5698-1234">
                                        </div>
                                        <div class="field">
                                            <label for="email">Email</label>
                                            <input id="email" type="email" value="masjid.alikhlas@gmail.com">
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label for="kapasitas">Kapasitas Jamaah</label>
                                        <input id="kapasitas" type="text" value="500">
                                    </div>

                                    <div class="field">
                                        <label for="alamat">Alamat</label>
                                        <textarea id="alamat">Jl. Raya Kebun Jeruk No. 12, Jakarta Barat</textarea>
                                    </div>

                                    <button type="button" class="primary-btn">Simpan Perubahan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="settings-panel" data-panel="users">
                    <div class="settings-panel-header">
                        <div>
                            <h2>Pengguna</h2>
                            <p>Kelola akun dan peran pengguna</p>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="settings-card-body users-panel">
                            <div class="panel-toolbar">
                                <div class="panel-title">Daftar Pengguna</div>
                                <button type="button" class="add-btn">+ Tambah</button>
                            </div>

                            <div class="user-list">
                                <div class="user-row">
                                    <div class="user-info">
                                        <div class="user-avatar">AF</div>
                                        <div>
                                            <div class="user-name">Ahmad Fauzi</div>
                                            <div class="user-role">Ketua DKM</div>
                                        </div>
                                    </div>
                                    <span class="role-badge admin">Admin</span>
                                </div>

                                <div class="user-row">
                                    <div class="user-info">
                                        <div class="user-avatar">BS</div>
                                        <div>
                                            <div class="user-name">Budi Santoso</div>
                                            <div class="user-role">Bendahara</div>
                                        </div>
                                    </div>
                                    <span class="role-badge">Bendahara</span>
                                </div>

                                <div class="user-row">
                                    <div class="user-info">
                                        <div class="user-avatar">CD</div>
                                        <div>
                                            <div class="user-name">Citra Dewi</div>
                                            <div class="user-role">Sekretaris</div>
                                        </div>
                                    </div>
                                    <span class="role-badge viewer">Viewer</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="settings-panel" data-panel="categories">
                    <div class="settings-panel-header">
                        <div>
                            <h2>Kategori</h2>
                            <p>Atur kategori pemasukan dan pengeluaran</p>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="settings-card-body">
                            <div class="panel-toolbar">
                                <div class="panel-title">Kategori Transaksi</div>
                                <button type="button" class="add-btn">+ Tambah</button>
                            </div>

                            <div class="category-list">
                                <div class="category-row">
                                    <div class="category-row-left">
                                        <span class="category-dot" style="background:#8b5cf6"></span>
                                        <span class="category-name">Infaq</span>
                                    </div>
                                    <div class="tag pemasukan">Pemasukan</div>
                                </div>

                                <div class="category-row">
                                    <div class="category-row-left">
                                        <span class="category-dot" style="background:#10b981"></span>
                                        <span class="category-name">Donasi</span>
                                    </div>
                                    <div class="tag pemasukan">Pemasukan</div>
                                </div>

                                <div class="category-row">
                                    <div class="category-row-left">
                                        <span class="category-dot" style="background:#2dd4bf"></span>
                                        <span class="category-name">Zakat</span>
                                    </div>
                                    <div class="tag pemasukan">Pemasukan</div>
                                </div>

                                <div class="category-row">
                                    <div class="category-row-left">
                                        <span class="category-dot" style="background:#3b82f6"></span>
                                        <span class="category-name">Operasional</span>
                                    </div>
                                    <div class="tag pengeluaran">Pengeluaran</div>
                                </div>

                                <div class="category-row">
                                    <div class="category-row-left">
                                        <span class="category-dot" style="background:#f59e0b"></span>
                                        <span class="category-name">Perbaikan</span>
                                    </div>
                                    <div class="tag pengeluaran">Pengeluaran</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="settings-panel" data-panel="notifications">
                    <div class="settings-panel-header">
                        <div>
                            <h2>Notifikasi</h2>
                            <p>Atur preferensi pemberitahuan</p>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="settings-card-body">
                            <div class="notification-list">
                                <div class="notification-row">
                                    <div class="notification-row-left">
                                        <div>
                                            <div class="category-name">Laporan bulanan otomatis</div>
                                            <span class="notification-copy">Kirim ringkasan keuangan setiap awal bulan</span>
                                        </div>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="notification-row">
                                    <div class="notification-row-left">
                                        <div>
                                            <div class="category-name">Peringatan defisit</div>
                                            <span class="notification-copy">Notifikasi saat pengeluaran melebihi pemasukan</span>
                                        </div>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="notification-row">
                                    <div class="notification-row-left">
                                        <div>
                                            <div class="category-name">Email transaksi baru</div>
                                            <span class="notification-copy">Kirim email setiap ada transaksi masuk</span>
                                        </div>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox">
                                        <span class="slider"></span>
                                    </label>
                                </div>

                                <div class="notification-row">
                                    <div class="notification-row-left">
                                        <div>
                                            <div class="category-name">Pengingat zakat</div>
                                            <span class="notification-copy">Notifikasi saat mendekati waktu zakat</span>
                                        </div>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div style="display:flex; justify-content:flex-end; margin-top:18px;">
                                <button type="button" class="primary-btn">Simpan Perubahan</button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>

<script>
    const tabs = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');
    const PROFILE_KEY = 'masjid_profile_name';
    const defaultName = 'Masjid Al-Ikhlas';

    function replaceMasjidName(newName) {
        if (!newName || !document.body) return;

        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const nodes = [];

        while (walker.nextNode()) {
            const node = walker.currentNode;
            if (node.textContent && node.textContent.includes(defaultName)) {
                nodes.push(node);
            }
        }

        nodes.forEach((node) => {
            node.textContent = node.textContent.replace(new RegExp(defaultName, 'g'), newName);
        });

        const brandName = document.querySelector('.brand-info strong');
        if (brandName) {
            brandName.textContent = newName;
        }
    }

    const namaMasjidInput = document.getElementById('nama-masjid');
    const savedProfile = localStorage.getItem(PROFILE_KEY);

    if (savedProfile) {
        const parsed = JSON.parse(savedProfile);
        if (parsed && parsed.name) {
            namaMasjidInput.value = parsed.name;
            replaceMasjidName(parsed.name);
        }
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((item) => item.classList.toggle('active', item === tab));
            panels.forEach((panel) => {
                panel.classList.toggle('active', panel.dataset.panel === tab.dataset.tab);
            });
        });
    });

    document.querySelectorAll('.primary-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const newName = (namaMasjidInput.value || '').trim() || defaultName;
            localStorage.setItem(PROFILE_KEY, JSON.stringify({ name: newName }));
            replaceMasjidName(newName);

            const originalText = button.textContent;
            button.textContent = 'Tersimpan';
            button.disabled = true;

            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
            }, 1200);
        });
    });
</script>
</body>
</html>
