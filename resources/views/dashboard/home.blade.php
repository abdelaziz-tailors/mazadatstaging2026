@extends('dashboard.layouts.app')

@section('title')
    {{ TranslationHelper::translate('home') }}
@endsection

@section('content')
    <div class="page-header">

        <div class="row ">
            <div class="col-sm-12">
                <h3 class="page-title">{{ TranslationHelper::translate('welcome') }}
                    {{ Auth::guard('admin')->user()->name }}!</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active">{{ TranslationHelper::translate('dashboard') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach ($reports as $report)
            <div class="col-xl-3 col-sm-6 col-12">
                <a href="">
                    <div class="card">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon text-{{ $report['color'] }} border-{{ $report['color'] }}">
                                    <i class="{{ $report['icon'] }}"></i>
                                </span>
                                <div class="dash-count">
                                    <h3>{{ $report['value'] }}</h3>
                                </div>
                            </div>
                            <div class="dash-widget-info">
                                <h6 class="text-muted">{{ $report['name'] }}</h6>
                                {{--
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-{{ $report['color'] }} w-50"></div>
                            </div>
                            --}}
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>


{{--    <div class="row">--}}
{{--        <div class="col-md-12 col-lg-6">--}}

{{--            <div class="card card-chart">--}}
{{--                <div class="card-header">--}}
{{--                    <h4 class="card-title">Revenue</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div id="morrisArea"></div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}
{{--        <div class="col-md-12 col-lg-6">--}}

{{--            <div class="card card-chart">--}}
{{--                <div class="card-header">--}}
{{--                    <h4 class="card-title">Status</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div id="morrisLine"></div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}
    </div>




    <div class="row">

{{--        <div class="col-md-6 d-flex">--}}

{{--            <div class="card card-table flex-fill">--}}
{{--                <div class="card-header" style="background-color: #1b5a90; color: white;">--}}
{{--                    <h4 class="card-title">{{ TranslationHelper::translate('Latest Doctor') }}</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                        <table class="table table-hover table-center mb-0">--}}
{{--                            <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>{{ TranslationHelper::translate('ID') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Name') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Speciality') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Status') }}</th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}

{{--                            @foreach ($tables['latest_doctors'] as $doctor)--}}
{{--                                <tr>--}}
{{--                                    <td>{{ $doctor->id }}</td>--}}

{{--                                    <td>--}}
{{--                                        <h2 class="table-avatar">--}}
{{--                                            <a href="#" class="avatar avatar-sm me-2"><img--}}
{{--                                                    class="avatar-img rounded-circle"--}}
{{--                                                    src="{{ checkImageExists($doctor->doctor_image) }}" alt=""></a>--}}
{{--                                            <a href="#">{{ $doctor->name }}</a>--}}
{{--                                        </h2>--}}
{{--                                    </td>--}}
{{--                                    <td>{{ $doctor->department->name ??'' }}</td>--}}

{{--                                    <td>--}}

{{--                                        @if ($doctor->is_approved == 2)--}}
{{--                                      <span class="badge badge-sm bg-danger">{{TranslationHelper::translate('Suspended')}}</span>--}}

{{--                                        @elseif ($doctor->is_approved == 1)--}}
{{--                                         <span class="badge badge-sm bg-dark">{{TranslationHelper::translate('Approved')}}</span>--}}

{{--                                        @else--}}
{{--                                        <span class="badge badge-sm bg-success">{{TranslationHelper::translate('Pending')}}</span>--}}
{{--                                        @endif--}}

{{--                                    </td>--}}

{{--                                </tr>--}}
{{--                            @endforeach--}}




{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}

{{--        <div class="col-md-6 d-flex">--}}

{{--            <div class="card card-table flex-fill">--}}
{{--                <div class="card-header" style="background-color: #1b5a90; color: white;">--}}
{{--                    <h4 class="card-title">{{ TranslationHelper::translate('Latest Clients') }}</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                        <table class="table table-hover table-center mb-0">--}}
{{--                            <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>{{ TranslationHelper::translate('ID') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Name') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Phone') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Last Visit	') }}</th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}

{{--                            @foreach ($tables['latest_users'] as $Clients)--}}
{{--                                <tr>--}}
{{--                                    <td>{{ $Clients->id }}</td>--}}

{{--                                    <td>--}}
{{--                                        <h2 class="">--}}
{{--                                          {{ $Clients->name }}--}}
{{--                                        </h2>--}}
{{--                                    </td>--}}
{{--                                    <td>{{ $Clients->phone  }}</td>--}}
{{--                                    <td>{{ $Clients->created_at  }}</td>--}}


{{--                                </tr>--}}
{{--                            @endforeach--}}


{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}

    </div>


    <div class="row">

{{--        <div class="col-md-6 d-flex">--}}

{{--            <div class="card card-table flex-fill">--}}
{{--                <div class="card-header" style="background-color: #1b5a90; color: white;">--}}
{{--                    <h4 class="card-title">{{ TranslationHelper::translate('Top Rated Doctor') }}</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                        <table class="table table-hover table-center mb-0">--}}
{{--                            <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>{{ TranslationHelper::translate('ID') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Name') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Rate') }}</th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr>--}}
{{--                                <td>ID  </td>--}}
{{--                                <td>Lab</td>--}}
{{--                                <td> 4.5</td>--}}
{{--                            </tr>--}}


{{--                            <tr>--}}
{{--                                <td>ID  </td>--}}
{{--                                <td>Lab</td>--}}
{{--                                <td> 4.5</td>--}}
{{--                            </tr>--}}


{{--                            <tr>--}}
{{--                                <td>ID  </td>--}}
{{--                                <td>Lab</td>--}}
{{--                                <td> 4.5</td>--}}
{{--                            </tr>--}}


{{--                            <tr>--}}
{{--                                <td>ID  </td>--}}
{{--                                <td>Lab</td>--}}
{{--                                <td> 4.5</td>--}}
{{--                            </tr>--}}



{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}

{{--        <div class="col-md-6 d-flex">--}}

{{--            <div class="card card-table flex-fill">--}}
{{--                <div class="card-header" style="background-color: #1b5a90; color: white;">--}}
{{--                    <h4 class="card-title">{{ TranslationHelper::translate('Top Rated Lab') }}</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                        <table class="table table-hover table-center mb-0">--}}
{{--                            <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>{{ TranslationHelper::translate('ID') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Name') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Rate') }}</th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}

{{--                            <tr>--}}
{{--                                <td>ID  </td>--}}
{{--                                <td>Lab</td>--}}
{{--                                <td> 4.5</td>--}}
{{--                            </tr>--}}


{{--                            <tr>--}}
{{--                                <td>ID  </td>--}}
{{--                                <td>Lab</td>--}}
{{--                                <td> 4.5</td>--}}
{{--                            </tr>--}}


{{--                            <tr>--}}
{{--                                <td>ID  </td>--}}
{{--                                <td>Lab</td>--}}
{{--                                <td> 4.5</td>--}}
{{--                            </tr>--}}


{{--                            <tr>--}}
{{--                                <td>ID  </td>--}}
{{--                                <td>Lab</td>--}}
{{--                                <td> 4.5</td>--}}
{{--                            </tr>--}}





{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}

{{--        <div class="col-md-6 d-flex">--}}

{{--            <div class="card card-table flex-fill">--}}
{{--                <div class="card-header" style="background-color: #1b5a90; color: white;">--}}
{{--                    <h4 class="card-title">{{ TranslationHelper::translate('Complaints') }}</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                        <table class="table table-hover table-center mb-0">--}}
{{--                            <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>{{ TranslationHelper::translate('ID') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('NO.') }}</th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                                    <tr>--}}
{{--                                        <td>5</td>--}}
{{--                                        <td>#124562</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td>6</td>--}}
{{--                                        <td>#124563</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td>7</td>--}}
{{--                                        <td>#124564</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td>8</td>--}}
{{--                                        <td>#124565</td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td>9</td>--}}
{{--                                        <td>#124566</td>--}}
{{--                                    </tr>--}}



{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}

{{--         <div class="col-md-6 d-flex">--}}

{{--            <div class="card card-table flex-fill">--}}
{{--                <div class="card-header" style="background-color: #1b5a90; color: white;">--}}
{{--                    <h4 class="card-title">{{ TranslationHelper::translate('Appointments of The week') }}</h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="table-responsive">--}}
{{--                        <table class="table table-hover table-center mb-0">--}}
{{--                            <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>{{ TranslationHelper::translate('Doctor') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate('Client') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate(' Date') }}</th>--}}
{{--                                    <th>{{ TranslationHelper::translate(' Date') }}</th>--}}
{{--                                </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody>--}}
{{--                            <tr>--}}
{{--                                <td>Doctor  </td>--}}
{{--                                <td>Client</td>--}}
{{--                                <td> date</td>--}}
{{--                                <td> date</td>--}}
{{--                            </tr>--}}

{{--                            <tr>--}}
{{--                                <td>Doctor  </td>--}}
{{--                                <td>Client</td>--}}
{{--                                <td> date</td>--}}
{{--                                <td> date</td>--}}
{{--                            </tr>--}}

{{--                            <tr>--}}
{{--                                <td>Doctor  </td>--}}
{{--                                <td>Client</td>--}}
{{--                                <td> date</td>--}}
{{--                                <td> date</td>--}}
{{--                            </tr>--}}

{{--                            <tr>--}}
{{--                                <td>Doctor  </td>--}}
{{--                                <td>Client</td>--}}
{{--                                <td> date</td>--}}
{{--                                <td> date</td>--}}
{{--                            </tr>--}}

{{--                            <tr>--}}
{{--                                <td>Doctor  </td>--}}
{{--                                <td>Client</td>--}}
{{--                                <td> date</td>--}}
{{--                                <td> date</td>--}}
{{--                            </tr>--}}


{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        </div>--}}

    </div>
@endsection
