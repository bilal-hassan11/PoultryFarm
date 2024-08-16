@extends('layouts.admin')
@section('content')

<div class="main-content app-content mt-5">
  <div class="side-app">
    <!-- CONTAINER -->
    <div class="main-container container-fluid">
        <!-- PAGE-HEADER -->
        <div class="row">
            <div class="col-12 col-sm-12">
                <div class="card ">
                    <div class="card-header">
                        <h3 class="card-title mb-0">{{@$title}} Filters</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.reports.all_reports_request') }}" id="form">
                            <div class="row">
                                <div class="col-md-3">
                                    <select id="accountFilter" class="form-control
                                    select2" name="account_id" id="account_id" >
                                        <option value="">Select Account</option>
                                        @foreach($acounts AS $ac)
                                            <option value="{{ $ac->hashid }}" @if(@$account_name[0]->id == $ac->id) selected @endif >{{ $ac->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="itemFilter" class="form-control select2" name="item_id" id="item_id">
                                        <option value="" >Select Item </option>
                                    @foreach($items AS $i)
                                        <option value="{{ $i->hashid }}" @if(@$item_name[0]->id == $i->id) selected @endif >{{ $i->name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" value="{{ (isset($is_update)) ? date('Y-m-d', strtotime($from_date)) : date('Y-m-d') }}" name="from_date" id="from_date">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" value="{{ (isset($is_update)) ? date('Y-m-d', strtotime($to_date)) : date('Y-m-d') }}" name="to_date" id="to_date">
                                </div>
                                <div class="col-md-2">
                                    <input  class="btn btn-primary" type="submit" >
                                    <input type="hidden" value="{{$id}}" id ="report_id" name="id" class="btn btn-primary ">


                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-3">
                <form action="{{ route('admin.reports.all_reports_pdf') }}" method="GET" target="_blank">
                    @csrf
                    <input type="hidden" name="account_id" id="accountInput">
                    <input type="hidden" name="item_id" id="itemInput">
                    <input type="hidden" name="from_date" id="fromdateInput">
                    <input type="hidden" name="to_date" id="todateInput">
                    <input type="hidden" name="id" id="idInput">

                    <input type="hidden" name="generate_pdf" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="ri-download-2-line"></i> Download PDF
                    </button>
                </form>
            </div>
        </div>
        @if(isset($from_date))
        <div class="row">
          <div class="col-12 col-sm-12">
              <div class="card ">
                <div class="card-body">
                <center>
                        <h2 style="color:green;  justify_content:center;"><span> <i class="glyphicon glyphicon-gift"></i> </span>{{ $title }}</h2>
                    <h4>From {{date('d-M-Y', strtotime($from_date))}} to {{date('d-M-Y', strtotime($to_date))}}</h4>
                        </center>

            </div>
              </div>
          </div>
          <!-- COL END -->
        </div>
        @endif
        <div class="row">
          <div class="col-12 col-sm-12">
              <div class="card ">
                <div class="card-header">
                    <h3 class="card-title mb-0">All {{@$title}} </h3>
                </div>
                <div class="card-body">
                <table id="example54" class="text-fade table table-bordered" style="width:100%">
                <thead>
                        <tr class="text-dark">
                            <th> Date  </th>
                            <th> Account Name </th>
                            <th> Item Name </th>
                            <th> Rate </th>
                            <th> Quantity </th>
                            <th> Net Value </th>
                            <!-- <th> Action </th> -->

                         </tr>
                    </thead>
                    <tbody>
                        @if( @$all_reports_values != "")
                            @foreach(@$all_reports_values AS $all)
                                <tr class="text-dark">
                                  <td><span class="waves-effect waves-light btn btn-rounded btn-primary-light">{{ date('d-m-y', strtotime(@$all->date)) }}</span></td>
                                  <td ><span class="waves-effect waves-light btn btn-outline btn-success">{{ @$all->account->name }}</span></td>
                                  <td><span class="waves-effect waves-light btn btn-outline btn-danger">{{ @$all->item->name }}</span></td>
                                  <td>{{    number_format(@$all->net_amount / @$all->quantity ,2) }}</td>
                                  <td>{{ @$all->quantity }}</td>
                                  <td ><span class="waves-effect waves-light btn btn-outline btn-success">{{ @$all->net_amount }}</span></td>
                                  <!-- <td>
                                        <button class="btn btn-outline-info  rounded-pill btn-wave" type="button" >
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <button class="btn btn-outline-info  rounded-pill btn-wave" type="button" >
                                            <i class="ri-download-2-line"></i>
                                        </button>
                                    </td> -->

                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

            </div>
              </div>
          </div>
          <!-- COL END -->
        </div>
    </div>
    <!-- CONTAINER END -->
  </div>
</div>


@endsection
@section('page-scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            $.ajax({
                type: 'GET',
                url: "{{ route('admin.reports.all_reports_pdf') }}",
                data: function(d) {
                        d.account_id = $('#accountInput').val();
                        d.item_id = $('#itemInput').val();
                        d.from_date = $('#fromdateInput').val();
                        d.to_date = $('#todateInput').val();
                        d.id = $('#idInput').val();

                    },
                success: function(response){

                    console.log(response);
                },
                error: function(blob){
                    console.log(blob);
                }
            });

            var id = $('#report_id').val();
                $('#idInput').val(id);

            var fromdate = $('#from_date').val();
                $('#fromdateInput').val(fromdate);

            var to_date = $('#to_date').val();
                $('#todateInput').val(to_date);

            $('#accountFilter').change(function() {
                var accountId = $(this).val();
                alert(accountId);
                $('#accountInput').val(accountId);

            });

            $('#itemFilter').change(function() {
                var itemId = $(this).val();
                $('#itemInput').val(itemId);

            });

            $('#from_date').change(function() {
                var fromdate = $(this).val();
                $('#fromdateInput').val(fromdate);

            });

            $('#to_date').change(function() {
                var to_date = $(this).val();
                $('#todateInput').val(to_date);

            });





        });
    </script>
@endsection
