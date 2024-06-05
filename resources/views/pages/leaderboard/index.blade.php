@extends('layouts.leaderboard')

@section('title', __('Leaderboard'))

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <div class="tab-content">

                        <div class="tab-pane fade text-center active show" id="tab-5" role="tabpanel">
                            <button class="btn btn-primary">{{ $board_name }} LEADERBOARD</button>
                            <button class="btn btn-square btn-success" disabled="">
                                {{ now()->format('d-M-Y') }}</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                                aria-label="#: activate to sort column ascending">Rank #</th>
                                            <th rowspan="1" colspan="1" style="width: 212px;" aria-sort="ascending"
                                                aria-label="Name: activate to sort column descending">Agent</th>

                                            <th rowspan="1" colspan="1" style="width: 97px;"
                                                aria-label="Status: activate to sort column ascending">Deals</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($agents->isEmpty())
                                            <tr>
                                                <td colspan="3">No results found.</td>
                                            </tr>
                                        @else
                                            @foreach ($agents as $row)
                                                @php
                                                    $leadCount = $row->leads;
                                                    $leadClass = '';

                                                    if ($leadCount == 0) {
                                                        $leadClass = 'danger';
                                                    } elseif ($leadCount >= 1 && $leadCount <= 5) {
                                                        $leadClass = 'warning';
                                                    } elseif ($leadCount >= 6 && $leadCount <= 10) {
                                                        $leadClass = 'info';
                                                    } elseif ($leadCount >= 11 && $leadCount <= 20) {
                                                        $leadClass = 'primary';
                                                    } else {
                                                        $leadClass = 'success';
                                                    }
                                                @endphp

                                                <tr class="{{ $loop->odd ? 'odd' : 'even' }}">
                                                    <td class="sorting_1">{{ $loop->iteration }}</td>
                                                    <td class="sorting_1">{{ $row->agent }}</td>
                                                    <td><span class="badge bg-{{ $leadClass }}"
                                                            style="font-size: x-large;">{{ $leadCount }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection
