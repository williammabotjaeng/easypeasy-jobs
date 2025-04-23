<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Background Job Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #6f42c1 0%, #4582ec 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            margin-bottom: 1.5rem;
        }
        .card-header {
            font-weight: 600;
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem 1.25rem;
        }
        .log-container {
            height: 300px;
            overflow-y: auto;
            background-color: #272822;
            color: #f8f8f2;
            border-radius: 0.25rem;
            padding: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-active {
            background-color: #28a745;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .btn-action {
            border-radius: 0.25rem;
        }
        .table th {
            border-top: none;
        }
        .stats-card {
            text-align: center;
            padding: 1.5rem;
        }
        .stats-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .stats-card .number {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .stats-card .label {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header text-center my-4">
            <h1 class="display-5 fw-bold">Background Job Dashboard</h1>
            <p class="lead">Monitor and manage your system's background tasks</p>
        </div>
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <i class="fas fa-play-circle text-primary"></i>
                    <div class="number">{{ count($runningJobs) }}</div>
                    <div class="label">Running Jobs</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <i class="fas fa-check-circle text-success"></i>
                    <div class="number">{{ count($jobLogs) }}</div>
                    <div class="label">Completed Jobs</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <i class="fas fa-exclamation-circle text-danger"></i>
                    <div class="number">{{ count($errorLogs) }}</div>
                    <div class="label">Failed Jobs</div>
                </div>
            </div>
        </div>
        
        <!-- Running Jobs -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-tasks me-2 text-primary"></i>Running Jobs
                </span>
                <span class="badge bg-primary">{{ count($runningJobs) }}</span>
            </div>
            <div class="card-body">
                @if(count($runningJobs) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Lock File</th>
                                    <th>Retry Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($runningJobs as $job)
                                    <tr>
                                        <td><span class="status-indicator status-active"></span></td>
                                        <td><code>{{ $job['filename'] }}</code></td>
                                        <td>
                                            <span class="badge bg-{{ $job['retryCount'] > 2 ? 'warning' : 'info' }}">
                                                {{ $job['retryCount'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('dashboard.jobs.cancel') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="lock_file" value="{{ $job['filename'] }}">
                                                <button type="submit" class="btn btn-danger btn-sm btn-action">
                                                    <i class="fas fa-stop me-1"></i>Cancel
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-coffee fa-3x text-muted mb-3"></i>
                        <p class="lead">No running jobs at the moment</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Logs Section -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-list-alt me-2 text-success"></i>Job Execution Log
                        </span>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="log-container">
@foreach($jobLogs as $log)
{{ $log }}
@endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Error Log
                        </span>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="log-container">
@foreach($errorLogs as $log)
{{ $log }}
@endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="mt-4 mb-5 text-center text-muted">
            <small>Background Job Dashboard &copy; 2025</small>
        </footer>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh the page every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Scroll log containers to bottom
        document.addEventListener('DOMContentLoaded', function() {
            const logContainers = document.querySelectorAll('.log-container');
            logContainers.forEach(function(container) {
                container.scrollTop = container.scrollHeight;
            });
        });
    </script>
</body>
</html>