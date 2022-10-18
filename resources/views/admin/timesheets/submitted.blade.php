@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')
@section('pageTitle', 'Timesheet List')
@section('content')
    <h3 class="page-title">Manage Timesheets</h3>
    <div class="panel panel-default">
        <div class="panel-heading">
           Submitted Timesheets 
           
        </div>

        <div class="panel-body table-responsive">
            @if (Session::has('successmsg'))
                <div class="col-md-12 alert alert-success"> 
                    {{ Session::get('successmsg') }}
                </div>
            @endif
            <table class="table table-bordered table-striped {{ count($timesheets) > 0 ? 'datatable' : '' }} @can('timesheet_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                <thead>
                    <tr>
                       <th style="text-align:center;"><input type="checkbox" id="select-all" /></th>
                       <th>Store Name</th>
                        <th>Event Date</th>
                        <th>Area</th>
                        <th>Run</th>
                        <th>Supervisor</th>
                        <th>Actions</th>
                   </tr>
                </thead>
                
                <tbody>
                    @if (count($timesheets) > 0)
                        @foreach ($timesheets as $timesheet)
                            <tr data-entry-id="{{ $timesheet->id }}">
                                @if ( request('show_deleted') != 1 )<td></td>@endif
                                <td>{{ @$timesheet->storename }}</td>
                                <td>{{ date('m-d-Y',strtotime(@$timesheet->dtJobDate)) }}</td>
                                <td>{{@$timesheet->area_number}}</td>
                                <td>{{ @$timesheet->run_number }}</td>
                                <td><?php echo @$timesheet->last_name.','.@$timesheet->first_name;?></td>
                                <td style="border-bottom: 1px solid;">
                                    @can('timesheet_view')
                                        <a href="{{ route('admin.timesheets.show',[$timesheet->id]) }}" class="btn btn-xs btn-primary" title="View Detail"><i class="fa fa-eye"></i></a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="14">@lang('global.app_no_entries_in_table')</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('javascript') 
    <script>
       
    </script>
@endsection