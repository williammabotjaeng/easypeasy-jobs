<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Background Job Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Background Job Dashboard</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                Running Jobs
            </div>
            <div class="card-body">
                @if(count($runningJobs) > 0)
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Lock File</th>
                                <th>Retry Count</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($runningJobs as $job)
                                <tr>
                                    <td>{{ $job['filename'] }}</td>
                                    <td>{{ $job['retryCount'] }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('dashboard.jobs.cancel') }}">
                                            @csrf
                                            <input type="hidden" name="lock_file" value="{{ $job['filename'] }}">
                                            <button type="submit" class="btn btn-danger btn-sm">Cancel Job</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No running jobs at the moment.</p>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        Job Execution Log
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-2 border" style="height: 300px; overflow-y: scroll;">
@foreach($jobLogs as $log)
{{ $log }}
@endforeach
                        </pre>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        Error Log
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-2 border" style="height: 300px; overflow-y: scroll;">
@foreach($errorLogs as $log)
{{ $log }}
@endforeach
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery via CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
