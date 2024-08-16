@extends('layouts.admin')
@section('content')

<div class="main-content app-content mt-5">
  <div class="side-app">
    <!-- CONTAINER -->
    <div class="main-container container-fluid">
        <!-- PAGE-HEADER -->


        <!-- COL END --> <!-- ROW-3 END --> <!-- ROW-5 -->

        <div class="row">
          <div class="col-12 col-sm-12">
              <div class="card ">
                <div class="card-header">
                    <h3 class="card-title mb-0">Add Shade Details</h3>
                </div>
                <div class="card-body">

                  <div class="card-block">
                    <div class="item_row">

                        <form class="ajaxForm" role="form" action="{{ route('admin.shades.store') }}" method="POST" novalidate>
                            @csrf
                              <div class="row">
                                <div class="col-md-3">
                                  <div class="form-group">
                                    <label>Date</label>
                                    <input class="form-control" type="date"  name="date" value="{{ (@$edit_shade->date != "") ? date('Y-m-d', strtotime(@$edit_shade->date)) : date('Y-d-d') }}" required>
                                  </div>
                                </div>
                                <input type="hidden" name="shade_id" value="{{ @$edit_shade->hashid }}">

                                <div class="col-md-3">
                                  <div class="form-group">
                                    <label>Shade Name </label>
                                    <input class="form-control" name="name" value="{{ @$edit_shade->name }}" required>
                                  </div>
                                </div>
                                <div class="col-md-3">
                                  <div class="form-group">
                                    <label>Status </label>
                                      <select class="form-control select2" name="status" id="status">
                                        <option value="active">Active</option>
                                        <option value="not_active">Not Active</option>
                                      </select>
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="">Address</label>
                                    <textarea class="form-control" name="address" id="address" cols="30" rows="4">{{ @$edit_shade->address }}</textarea>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col-md-3">
                                  <div class="form-group">
                                    <button type="submit" value="submit" name="save_shade" class="btn btn-success"> Save </button>
                                  </div>
                                </div>
                              </div>
                            </form>
                      <br /><br />
                    </div>

                  </div>
                </div>
              </div>
          </div>
          <!-- COL END -->
        </div>
        <!-- ROW-5 END -->


        <div class="row">
          <div class="col-12 col-sm-12">
              <div class="card ">
                <div class="card-header">
                    <h3 class="card-title mb-0">All Shades Details </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                      <div id="data-table_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">

                            <div class="row">
                              <div class="col-sm-12">
                                <table id="example5" class="text-fade table table-bordered" style="width:100%">
                                    <thead>
                                        <tr class="text-dark">
                                            <th>Start date</th>
                                            <th>Shade Name</th>
                                            <th>Status</th>
                                            <th>Address</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      @foreach($shade AS $s)
                                        <tr>
                                            <td>{{ date('d-M-Y', strtotime( @$s->date)) }}</td>
                                            <td>{{ $s->name }}</td>
                                            <td>{{ $s->status }}</td>
                                            <td>{{ $s->address }}</td>
                                            <td style="width: 20%; !important">

                                                <a class="btn btn-outline-info rounded-pill btn-wave mr-2"
                                                    href="{{route('admin.shades.edit', $s->hashid)}}"
                                                    title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </a>

                                            </td>

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

    </div>
    <!-- CONTAINER END -->
  </div>
</div>

@endsection





