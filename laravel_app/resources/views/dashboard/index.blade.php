@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Tổng quan hoạt động thư viện')

@section('content')
<section class="stats-grid">
    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted">Tổng đầu sách</div>
                <div class="stat-value">{{ $stats['totalBooks'] }}</div>
            </div>
            <div class="stat-icon"><i class="bi bi-book"></i></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted">Sách đang mượn</div>
                <div class="stat-value">{{ $stats['borrowingBooks'] }}</div>
            </div>
            <div class="stat-icon"><i class="bi bi-arrow-left-right"></i></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted">Tổng độc giả</div>
                <div class="stat-value">{{ $stats['totalReaders'] }}</div>
            </div>
            <div class="stat-icon"><i class="bi bi-people"></i></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted">Tổng số lượng sách</div>
                <div class="stat-value">{{ $stats['totalCopies'] }}</div>
            </div>
            <div class="stat-icon"><i class="bi bi-collection"></i></div>
        </div>
    </div>
</section>

<section class="grid-two">
    <div class="content-card p-3">
        <h3 class="section-title h4 mb-3">Top 5 độc giả mượn nhiều nhất</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Tên độc giả</th>
                    <th>Tổng lượt</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($topBorrowers as $row)
                    <tr>
                        <td>{{ $row->tenDG }}</td>
                        <td>{{ $row->tongLuot }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Chưa có dữ liệu</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="content-card p-3">
        <h3 class="section-title h4 mb-3">Top 5 sách mượn nhiều nhất</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Tên sách</th>
                    <th>Tổng mượn</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($topBooks as $row)
                    <tr>
                        <td>{{ $row->tenSach }}</td>
                        <td>{{ $row->tongMuon }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Chưa có dữ liệu</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="content-card p-3">
    <h3 class="section-title h4 mb-3">Top 10 phiếu mượn quá hạn</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>Mã phiếu</th>
                <th>Độc giả</th>
                <th>Hạn trả</th>
                <th>Trạng thái</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($overdues as $row)
                <tr>
                    <td>{{ $row->maMT }}</td>
                    <td>{{ $row->tenDG }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($row->hanTra)->format('d/m/Y') }}</td>
                    <td><span class="badge-soft-danger">{{ $row->trangThai }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Không có phiếu quá hạn</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
