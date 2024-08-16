@extends('layouts.admin')
@section('content')
<div class="main-content app-content mt-6">
  <div class="side-app">
    <!-- CONTAINER -->
    <div class="main-container container-fluid">
        <!-- PAGE-HEADER -->

        <!-- PAGE-HEADER END --> <!-- ROW-1 -->

        <!-- COL END --> <!-- ROW-3 END --> <!-- ROW-5 -->

         <div class="row">
          <div class="col-12 col-sm-12">
              <div class="card ">
                <div class="card-header">
                    <h3 class="card-title mb-0">All ITems Detail</h3>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <div id="data-table_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                      <div class="row">
                          <div class="col-sm-12">
                            <table id="example54" class="table table-bordered text-nowrap mb-0 dataTable no-footer" role="grid" aria-describedby="data-table_info">

                              <thead>
                                <tr class="text-dark">
                                  <th>S.No</th>
                                  <th>Category</th>
                                  <th>Item <br />Name</th>
                                  <th>Available <br />Stock</th>
                                  <th>Rate</th>
                                  <th>Stock value</th>

                                  <th>Stock <br /> Status</th>
                                  <th>Item <br />Status</th>
                                  <th>Remarks</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php $tot = 0; ?>
                                @foreach($items AS $item)
                                  <tr class="text-dark">
                                      <td>{{ $loop->iteration }}</td>
                                      <td>{{ $item->category->name ?? "" }}</td>
                                      <td>{{ $item->name }}</td>
                                      <td>{{ $item->stock_qty }}</td>
                                      <td>{{ number_format($item->price, 2) }}</td>
                                      <?php $tot +=  $item->price * $item->stock_qty; ?>
                                      <td>{{ $item->price * $item->stock_qty }}</td>

                                      <td>
                                          @if($item->stock_status == 1)
                                              Enabled
                                          @else
                                              Disabled
                                          @endif
                                      </td>
                                      <td>
                                          @if($item->status == 1)
                                              Active
                                          @else
                                              Deactive
                                          @endif
                                      </td>
                                      <td>{!! wordwrap($item->remarks, 10, "<br />\n", true) !!}</td>
                                      <td width="120">
                                        <div class="btn-list">
                                          <a  href="{{route('admin.items.edit', $item->hashid)}}" class="btn btn-icon btn-primary btn-wave waves-effect waves-light" data-bs-toggle="tooltip" data-bs-original-title="Edit"> <i class="ri-pencil-fill lh-1"></i> </a>
                                        </div>

                                          <!-- <button type="button" onclick="ajaxRequest(this)" data-url="{{ route('admin.items.delete', $item->hashid) }}"  class="waves-effect waves-light btn btn-rounded btn-primary-light">
                                              <i class="fas fa-trash"></i>
                                          </button> -->
                                      </td>
                                  </tr>
                                @endforeach
                              </tbody>
                              <tfoot>
                                <tr class="text-dark">
                                  <td colspan="4">Total:</td>
                                  <td>{{$tot}}</td>
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
          <!-- COL END -->
        </div>
    </div>
    <!-- CONTAINER END -->
  </div>



@endsection

@section('page-scripts')
@include('admin.partials.datatable')
@endsection
