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
                        <form class="ajaxForm" role="form" action="{{ route('admin.mortalitys.store') }}" method="POST" novalidate>
                            @csrf
                              <div class="row">
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>Date</label>
                                    <input class="form-control" type="date"   name="date" value="{{ (isset($is_update)) ? date('Y-m-d', strtotime(@$edit_flock->date)) : date('Y-m-d') }}" required>
                                  </div>
                                </div>
                                <input type="hidden" name="flock_id" value="{{ @$edit_flock->hashid }}">

                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>Shade </label>
                                    <select class="form-control select2" name="shade_id" id="shade_id">
                                      <option value="">Select Shade</option>
                                      @foreach($shade as $s)
                                      <option  value="{{ $s->hashid }}" @if(@$edit_flock->shade_id == $s->id) selected @endif>{{ $s->name }}</option>
                                      @endforeach
                                    </select>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>Mortality Quantity</label>
                                    <input class="form-control" type="number" name="quantity" value="{{ @$edit_flock->quantity }}" required>
                                  </div>
                                </div>

                              </div>
                              <div class="row">
                                <div class="col-md-3">
                                  <div class="form-group">
                                    <button type="submit" value="submit" name="save_flock_mortality" class="btn btn-success"> Save </button>
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
                    <h3 class="card-title mb-0">All Shades Mortality Details </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                      <div id="data-table_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">

                            <div class="row">
                                <div class="col-sm-12">
                                    <table id="example54" class="text-fade table table-bordered" style="width:100%">
                                        <thead>
                                            <tr class="text-dark">
                                                <th> Mortality Date</th>
                                                <th>Shade Name</th>
                                                <th>Shade Mortality</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $tot = 0 ; ?>
                                        @foreach($mortality AS $m)
                                            <tr>
                                                <td>{{ date('d-M-Y', strtotime( @$m->date)) }}</td>
                                                <td>{{ @$m->shade->name }}</td>
                                                <?php $tot += @$m->quantity ; ?>
                                                <td>{{ @$m->quantity }}</td>
                                                <td style="width: 20%; !important">

                                                    <a class="btn btn-outline-info rounded-pill btn-wave mr-2"
                                                        href="{{route('admin.mortalitys.edit', $m->hashid)}}"
                                                        title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </a>

                                                </td>

                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Total</th>
                                                <th>-</th>
                                                <th>{{ @$tot }}</th>
                                                <th>-</th>
                                            </tr>
                                        </tfoot>
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

