@extends('layouts.admin.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value">1,234</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-value">45</div>
                <div class="stat-label">Active Events</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="bi bi-eye"></i>
                </div>
                <div class="stat-value">12.5K</div>
                <div class="stat-label">Page Views</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="stat-value">+12.5%</div>
                <div class="stat-label">Growth Rate</div>
            </div>
        </div>
    </div>

    <!-- Main Content Cards -->
    <div class="row g-4">
        <div class="col-md-12">
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                    <button class="btn-admin btn-admin-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                        Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 12px;">JD</div>
                                            <span>John Doe</span>
                                        </div>
                                    </td>
                                    <td>Registered for event</td>
                                    <td>2 hours ago</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 12px;">JS</div>
                                            <span>Jane Smith</span>
                                        </div>
                                    </td>
                                    <td>Created new account</td>
                                    <td>5 hours ago</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 12px;">MJ</div>
                                            <span>Mike Johnson</span>
                                        </div>
                                    </td>
                                    <td>Updated profile</td>
                                    <td>1 day ago</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
