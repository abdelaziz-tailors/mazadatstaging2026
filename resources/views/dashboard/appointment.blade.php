@extends('dashboard.layouts.app')

@section('title')
    {{ TranslationHelper::translate('Appointment') }}
@endsection

@section('content')
    <div class="page-header">
        <div class="row">
            <div class="col-sm-12">
                <h3 class="page-title">{{ TranslationHelper::translate('Appointment') }} </h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard.index')}}">{{ TranslationHelper::translate('dashboard') }}</a></li>
                    <li class="breadcrumb-item active">{{ TranslationHelper::translate('Appointment') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer"><div class="row"><div class="col-sm-12 col-md-6"><div class="dataTables_length" id="DataTables_Table_0_length"><label>Show <select name="DataTables_Table_0_length" aria-controls="DataTables_Table_0" class="custom-select custom-select-sm form-control form-control-sm"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select> entries</label></div></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12"><table class="datatable table table-hover table-center mb-0 dataTable no-footer" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info">
                                        <thead>
                                        <tr role="row"><th class="sorting_asc" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Doctor Name: activate to sort column descending" style="width: 263.675px;">Doctor Name</th><th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Speciality: activate to sort column ascending" style="width: 112.775px;">Speciality</th><th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Patient Name: activate to sort column ascending" style="width: 209px;">Patient Name</th><th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Apointment Time: activate to sort column ascending" style="width: 185.413px;">Apointment Time</th><th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" style="width: 77.175px;">Status</th><th class="text-end sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Amount: activate to sort column ascending" style="width: 93.5625px;">Amount</th></tr>
                                        </thead>
                                        <tbody>










                                        <tr role="row" class="odd">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-02.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Darren Elder</a>
                                                </h2>
                                            </td>
                                            <td>Dental</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient2.jpg" alt="User Image"></a>
                                                    <a href="">Travis Trimble </a>
                                                </h2>
                                            </td>
                                            <td>5 Nov 2019 <span class="text-primary d-block">11.00 AM - 11.35 AM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_2" class="check" checked="">
                                                    <label for="status_2" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $300.00
                                            </td>
                                        </tr><tr role="row" class="even">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-03.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Deborah Angel</a>
                                                </h2>
                                            </td>
                                            <td>Cardiology</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient3.jpg" alt="User Image"></a>
                                                    <a href="">Carl Kelly</a>
                                                </h2>
                                            </td>
                                            <td>11 Nov 2019 <span class="text-primary d-block">12.00 PM - 12.15 PM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_3" class="check" checked="">
                                                    <label for="status_3" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $150.00
                                            </td>
                                        </tr><tr role="row" class="odd">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-09.jpg" alt="User Image"></a>
                                                    <a href="">Dr. John Gibbs</a>
                                                </h2>
                                            </td>
                                            <td>Dental</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient9.jpg" alt="User Image"></a>
                                                    <a href="">Walter Roberson</a>
                                                </h2>
                                            </td>
                                            <td>21 Nov 2019 <span class="text-primary d-block">12.10 PM - 12.25 PM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_5" class="check" checked="">
                                                    <label for="status_5" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $300.00
                                            </td>
                                        </tr><tr role="row" class="even">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-06.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Katharine Berthold</a>
                                                </h2>
                                            </td>
                                            <td>Orthopaedics</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient6.jpg" alt="User Image"></a>
                                                    <a href="">Elsie Gilley</a>
                                                </h2>
                                            </td>
                                            <td>16 Nov 2019 <span class="text-primary d-block">1.00 PM - 1.15 PM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_5" class="check" checked="">
                                                    <label for="status_5" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $250.00
                                            </td>
                                        </tr><tr role="row" class="odd">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-07.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Linda Tobin</a>
                                                </h2>
                                            </td>
                                            <td>Neurology</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient7.jpg" alt="User Image"></a>
                                                    <a href="">Joan Gardner</a>
                                                </h2>
                                            </td>
                                            <td>18 Nov 2019 <span class="text-primary d-block">1.10 PM - 1.25 PM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_5" class="check" checked="">
                                                    <label for="status_5" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $260.00
                                            </td>
                                        </tr><tr role="row" class="even">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-05.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Marvin Campbell</a>
                                                </h2>
                                            </td>
                                            <td>Orthopaedics</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient5.jpg" alt="User Image"></a>
                                                    <a href="">Gina Moore</a>
                                                </h2>
                                            </td>
                                            <td>15 Nov 2019 <span class="text-primary d-block">1.00 PM - 1.15 PM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_5" class="check" checked="">
                                                    <label for="status_5" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $200.00
                                            </td>
                                        </tr><tr role="row" class="odd">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-10.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Olga Barlow</a>
                                                </h2>
                                            </td>
                                            <td>Dental</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient10.jpg" alt="User Image"></a>
                                                    <a href="">Robert Rhodes</a>
                                                </h2>
                                            </td>
                                            <td>23 Nov 2019 <span class="text-primary d-block">12.10 PM - 12.25 PM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_5" class="check" checked="">
                                                    <label for="status_5" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $300.00
                                            </td>
                                        </tr><tr role="row" class="even">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-08.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Paul Richard</a>
                                                </h2>
                                            </td>
                                            <td>Dermatology</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient8.jpg" alt="User Image"></a>
                                                    <a href=""> Daniel Griffing</a>
                                                </h2>
                                            </td>
                                            <td>18 Nov 2019 <span class="text-primary d-block">11.10 AM - 11.25 AM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_5" class="check" checked="">
                                                    <label for="status_5" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $260.00
                                            </td>
                                        </tr><tr role="row" class="odd">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-01.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Ruby Perrin</a>
                                                </h2>
                                            </td>
                                            <td>Dental</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient1.jpg" alt="User Image"></a>
                                                    <a href="">Charlene Reed </a>
                                                </h2>
                                            </td>
                                            <td>9 Nov 2019 <span class="text-primary d-block">11.00 AM - 11.15 AM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_1" class="check" checked="">
                                                    <label for="status_1" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $200.00
                                            </td>
                                        </tr><tr role="row" class="even">
                                            <td class="sorting_1">
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/doctors/doctor-thumb-04.jpg" alt="User Image"></a>
                                                    <a href="">Dr. Sofia Brient</a>
                                                </h2>
                                            </td>
                                            <td>Urology</td>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="{{asset('/')}}assets/img/patients/patient4.jpg" alt="User Image"></a>
                                                    <a href=""> Michelle Fairfax</a>
                                                </h2>
                                            </td>
                                            <td>7 Nov 2019 <span class="text-primary d-block">1.00 PM - 1.20 PM</span></td>
                                            <td>
                                                <div class="status-toggle">
                                                    <input type="checkbox" id="status_4" class="check" checked="">
                                                    <label for="status_4" class="checktoggle">checkbox</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                $150.00
                                            </td>
                                        </tr></tbody>
                                    </table></div></div><div class="row"><div class="col-sm-12 col-md-5"><div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">Showing 1 to 10 of 10 entries</div></div><div class="col-sm-12 col-md-7"><div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate"><ul class="pagination"><li class="paginate_button page-item previous disabled" id="DataTables_Table_0_previous"><a href="#" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="0" class="page-link">Previous</a></li><li class="paginate_button page-item active"><a href="#" aria-controls="DataTables_Table_0" data-dt-idx="1" tabindex="0" class="page-link">1</a></li><li class="paginate_button page-item next disabled" id="DataTables_Table_0_next"><a href="#" aria-controls="DataTables_Table_0" data-dt-idx="2" tabindex="0" class="page-link">Next</a></li></ul></div></div></div></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
