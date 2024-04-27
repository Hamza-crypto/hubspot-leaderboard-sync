@extends('layouts.leaderboard')

@section('title', __('Leaderboard'))

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="datatables-clients_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">

                        <div class="row dt-row">
                            <div class="col-sm-12">
                                <table id="datatables-clients" class="table table-striped dataTable no-footer dtr-inline"
                                    style="width: 100%;" aria-describedby="datatables-clients_info">
                                    <thead>
                                        <tr>
                                            <th rowspan="1" colspan="1" style="width: 56px;"
                                                aria-label="#: activate to sort column ascending">#</th>
                                            <th rowspan="1" colspan="1" style="width: 212px;" aria-sort="ascending"
                                                aria-label="Name: activate to sort column descending">Agent</th>

                                            <th rowspan="1" colspan="1" style="width: 97px;"
                                                aria-label="Status: activate to sort column ascending">Leads</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($agents as $row)
                                            <tr class="{{ $loop->odd ? 'odd' : 'even' }}">
                                                <td class="sorting_1">{{ $loop->iteration }}</td>
                                                <td class="sorting_1">{{ $row->agent }}</td>
                                                <td><span class="badge bg-success">{{ $row->leads }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
