@extends('layouts.admin')

@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <br /><br />
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12">
                        <div class="row total-sales-card-section">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                <div class="card custom-card overflow-hidden">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h6 class="fw-normal fs-14">Total Sales</h6>
                                                <h3 class="mb-2 number-font fs-24">34,516</h3>
                                                <p class="text-muted mb-0"> <span class="text-primary"> <i
                                                            class="ri-arrow-up-s-line bg-primary text-white rounded-circle fs-13 p-0 fw-semibold align-bottom"></i>
                                                        3%</span> last month </p>
                                            </div>
                                            <div class="col col-auto mt-2">
                                                <div
                                                    class="counter-icon bg-primary-gradient box-shadow-primary rounded-circle ms-auto mb-0">
                                                    <i class="fe fe-trending-up mb-5 "></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                <div class="card custom-card overflow-hidden">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h6 class="fw-normal fs-14">Total Leads</h6>
                                                <h3 class="mb-2 number-font fs-24">56,992</h3>
                                                <p class="text-muted mb-0"> <span class="text-secondary"> <i
                                                            class="ri-arrow-up-s-line bg-secondary text-white rounded-circle fs-13 p-0 fw-semibold align-bottom"></i>
                                                        3%</span> last month </p>
                                            </div>
                                            <div class="col col-auto mt-2">
                                                <div
                                                    class="counter-icon bg-danger-gradient box-shadow-danger rounded-circle  ms-auto mb-0">
                                                    <i class="ri-rocket-line mb-5  "></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                <div class="card custom-card overflow-hidden">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h6 class="fw-normal fs-14">Total Profit</h6>
                                                <h3 class="mb-2 number-font fs-24">$42,567</h3>
                                                <p class="text-muted mb-0"> <span class="text-success"> <i
                                                            class="ri-arrow-down-s-line bg-primary text-white rounded-circle fs-13 p-0 fw-semibold align-bottom"></i>
                                                        0.5%</span> last month </p>
                                            </div>
                                            <div class="col col-auto mt-2">
                                                <div
                                                    class="counter-icon bg-secondary-gradient box-shadow-secondary rounded-circle ms-auto mb-0">
                                                    <i class="fe fe-dollar-sign  mb-5 "></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                                <div class="card custom-card overflow-hidden">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col">
                                                <h6 class="fw-normal fs-14">Total Cost</h6>
                                                <h3 class="mb-2 number-font fs-24">$34,78953</h3>
                                                <p class="text-muted mb-0"> <span class="text-danger"> <i
                                                            class="ri-arrow-down-s-line bg-danger text-white rounded-circle fs-13 p-0 fw-semibold align-bottom"></i>
                                                        0.2%</span> last month </p>
                                            </div>
                                            <div class="col col-auto mt-2">
                                                <div
                                                    class="counter-icon bg-success-gradient box-shadow-success rounded-circle  ms-auto mb-0">
                                                    <i class="fe fe-briefcase mb-5 "></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br /><br />
            </div>
            <!-- Feed -->
            <div class="row">

                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card custom-card">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-primary">
                                    <i class="fe fe-file-text mx-2 fs-20 text-primary-shadow  align-middle"></i>
                                </span>
                                Total Feed Purchase
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ @$tot_purchase_feed_ammount ?? 0 }}</h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped bg-primary w-70" role="progressbar">

                                </div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted"> Bags </span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_purchase_feed_begs ?? 0 }}</h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font2">{{ @$tot_purchase_feed_ammount ?? 0 }}</h4>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-secondary">
                                    <i class="ri-group-line mx-2 fs-20 text-secondary-shadow align-middle"></i>
                                </span>
                                Total Feed Sale
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ @$tot_sale_feed_ammount ?? 0 }}</h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped  bg-secondary w-50" role="progressbar"></div>
                            </div>
                            <div class="row mt-4">


                                <div class="col text-center">

                                    <span class="text-muted">Bags</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_feed_begs ?? 0 }}</h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_feed_ammount ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-success">
                                    <i class="fe fe-award mx-2 fs-20 text-success-shadow  align-middle"></i>
                                </span>
                                Total Feed Sale Return
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ @$tot_purchase_feed_ammount ?? 0 }}</h4>

                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped  bg-success w-60" role="progressbar">

                                </div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted">Bags</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_return_feed_begs ?? 0 }}
                                    </h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_return_feed_ammount ?? 0 }}
                                    </h4>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-info">
                                    <i class="fe fe-tag mx-2 fs-20 text-info-shadow  align-middle"></i>
                                </span>
                                Total Feed Purchase Return
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">
                                {{ $tot_purchase_return_feed_ammount ?? 0 }}
                            </h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-info w-40"
                                    role="progressbar"></div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted">Qty</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ $tot_purchase_return_feed_begs ?? 0 }}
                                    </h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">
                                        {{ $tot_purchase_return_feed_ammount ?? 0 }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Medicine -->
            <div class="row">

                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card custom-card">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-primary">
                                    <i class="fa fa-medkit mx-2 fs-20 text-primary-shadow  align-middle"></i>
                                </span>
                                Total Medicine Purchase
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ @$tot_purchase_medicine_ammount ?? 0 }}
                            </h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped bg-primary w-70" role="progressbar">

                                </div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted"> Qty </span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_purchase_medicine_qty ?? 0 }}
                                    </h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font2">
                                        {{ @$tot_purchase_medicine_ammount ?? 0 }}</h4>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-secondary">
                                    <i class="fa fa-medkit mx-2 fs-20 text-secondary-shadow align-middle"></i>
                                </span>
                                Total Medicine Sale
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ @$tot_sale_medicine_ammount ?? 0 }}
                            </h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped  bg-secondary w-50" role="progressbar">
                                </div>
                            </div>
                            <div class="row mt-4">


                                <div class="col text-center">

                                    <span class="text-muted">Qty</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_medicine_qty ?? 0 }}</h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_medicine_ammount ?? 0 }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-success">
                                    <i class="fa fa-medkit mx-2 fs-20 text-success-shadow  align-middle"></i>
                                </span>
                                Total Medicine Sale Return
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">
                                {{ @$tot_sale_return_medicine_ammount ?? 0 }}</h4>

                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped  bg-success w-60" role="progressbar">

                                </div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted">Qty</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_return_medicine_qty ?? 0 }}
                                    </h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">
                                        {{ @$tot_sale_return_medicine_ammount ?? 0 }}</h4>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-info">
                                    <i class="fa fa-medkit mx-2 fs-20 text-info-shadow  align-middle"></i>
                                </span>
                                Total Medicine Purchase Return
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">
                                {{ $tot_purchase_return_medicine_ammount ?? 0 }}
                            </h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-info w-40"
                                    role="progressbar"></div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted">Qty</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">
                                        {{ $tot_purchase_return_medicine_qty ?? 0 }}</h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">
                                        {{ $tot_purchase_return_medicine_ammount ?? 0 }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Chick And Murghi -->
            <div class="row">

                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card custom-card">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-primary">
                                    <i class="fa fa-qq mx-2 fs-20 text-primary-shadow  align-middle"></i>
                                </span>
                                Total chick Purchase
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ @$tot_purchase_chick_ammount ?? 0 }}
                            </h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped bg-primary w-70" role="progressbar">

                                </div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted"> Qty </span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_purchase_chick_qty ?? 0 }}</h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font2">{{ @$tot_purchase_chick_ammount ?? 0 }}
                                    </h4>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-secondary">
                                    <i class="fa fa-qq mx-2 fs-20 text-secondary-shadow align-middle"></i>
                                </span>
                                Total Chick Sale
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ @$tot_sale_chick_ammount ?? 0 }}</h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped  bg-secondary w-50" role="progressbar">
                                </div>
                            </div>
                            <div class="row mt-4">


                                <div class="col text-center">

                                    <span class="text-muted">Qty</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_chick_qty ?? 0 }}</h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_sale_chick_ammount ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-12 col-lg-6">

                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-success">
                                    <i class="fa fa-android mx-2 fs-20 text-success-shadow  align-middle"></i>
                                </span>
                                Total Murghi Purchase
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ @$tot_purchase_murghi_ammount ?? 0 }}
                            </h4>

                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped  bg-success w-60" role="progressbar">

                                </div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted">Weight</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_purchase_murghi_qty ?? 0 }}</h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ @$tot_purchase_murghi_ammount ?? 0 }}
                                    </h4>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-12 col-lg-6">
                    <div class="card overflow-hidden">

                        <div class="card-body text-center">

                            <h6 class="">
                                <span class="text-info">
                                    <i class="fa fa-android mx-2 fs-20 text-info-shadow  align-middle"></i>
                                </span>
                                Total Murghi Sale
                            </h6>
                            <h4 class="text-dark counter mt-0 mb-3 number-font">{{ $tot_sale_murghi_ammount ?? 0 }}
                            </h4>
                            <div class="progress h-1 mt-0 mb-2">

                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-info w-40"
                                    role="progressbar"></div>
                            </div>
                            <div class="row mt-4">

                                <div class="col text-center">

                                    <span class="text-muted">Weight</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ $tot_sale_murghi_qty ?? 0 }}</h4>
                                </div>
                                <div class="col text-center">

                                    <span class="text-muted">Ammount</span>
                                    <h4 class="fw-normal mt-2 mb-0 number-font1">{{ $tot_sale_murghi_ammount ?? 0 }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CashBook -->
            <div class="row row-cards">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card custom-card">
                        <div class="card-header pb-0 border-bottom-0">
                            <h3 class="card-title">Total Credit</h3>
                        </div>
                        <div class="card-body pt-0">
                            <h3 class="d-inline-block mb-2">{{ @$tot_credit ?? 0 }}</h3>
                            <div class="progress h-2 mt-2 mb-2">
                                <div class="progress-bar bg-primary w-50" role="progressbar"></div>
                            </div>
                            <div class="float-start">
                                <div class="mt-2">
                                    <i class="fa fa-caret-up text-success"></i>
                                    <span>12% increase</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card custom-card">
                        <div class="card-header pb-0 border-bottom-0">
                            <h3 class="card-title">Total Debit</h3>
                        </div>
                        <div class="card-body pt-0">
                            <h3 class="d-inline-block mb-2">{{ @$tot_debit ?? 0 }}</h3>
                            <div class="progress h-2 mt-2 mb-2">
                                <div class="progress-bar bg-success w-50" role="progressbar"></div>
                            </div>
                            <div class="float-start">
                                <div class="mt-2">
                                    <i class="fa fa-caret-down text-success"></i>
                                    <span>5% decrease</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card custom-card">
                        <div class="card-header pb-0 border-bottom-0">
                            <h3 class="card-title">Total Expense</h3>
                        </div>
                        <div class="card-body pt-0">
                            <h3 class="d-inline-block mb-2">{{ @$tot_expense ?? 0 }}</h3>
                            <div class="progress h-2 mt-2 mb-2">
                                <div class="progress-bar bg-warning w-50" role="progressbar"></div>
                            </div>
                            <div class="float-start">
                                <div class="mt-2">
                                    <i class="fa fa-caret-up text-warning"></i>
                                    <span>10% increase</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card custom-card">
                        <div class="card-header pb-0 border-bottom-0">
                            <h3 class="card-title">Cash In Hand</h3>
                        </div>
                        <div class="card-body pt-0">
                            <h3 class="d-inline-block mb-2">{{ @$tot_cash_in_hand ?? 0 }}</h3>
                            <div class="progress h-2 mt-2 mb-2">
                                <div class="progress-bar bg-danger w-50" role="progressbar"></div>
                            </div>
                            <div class="float-start">
                                <div class="mt-2">
                                    <i class="fa fa-caret-down text-danger"></i>
                                    <span>15% decrease</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- COL END -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5>Low Stock Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-fit datatable" id="sellingTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Expiry</th>
                                            <th>Available Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lowStockAlertProducts as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->name ?? 0 }}</td>
                                                <td>{{ $item->expiry_date ?? '' }}</td>
                                                <td style="text-align: right">{{ $item->quantity ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Near Expiry Products</h5>
                            <select id="expiryDropdown" class="form-select" style="width: auto;" value="1">
                                <option value="1">1 Month</option>
                                <option value="2">2 Months</option>
                                <option value="3">3 Months</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive deals-table">
                                <table class="table text-nowrap table-hover border table-bordered" id="sellingTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Item</th>
                                            <th>Expiry</th>
                                            <th>Available Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody id="expiredItemsTbody">
                                        @foreach ($expired_items as $item)
                                            <tr>
                                                <td>{{ $item->id ?? 0 }}</td>
                                                <td>{{ $item->name ?? 0 }}</td>
                                                <td>{{ $item->expiry_date ?? '' }}</td>
                                                <td style="text-align: right">{{ $item->total_quantity ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5>Max Sold Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-fit datatable" id="sellingTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($maxSellingProducts as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->item->name ?? 0 }}</td>
                                                <td style="text-align: right">{{ abs($item->total_quantity) ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5>Low Selling Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-fit datatable" id="sellingTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lowSellingProducts as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->item->name ?? 0 }}</td>
                                                <td style="text-align: right">{{ abs($item->total_quantity) ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
               
                <!-- <div id="hightChart">

                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                            <div id="consumption_chart">

                                                                                                                                                                                                                                                                                                            </div>

                                                                                                                                                                                                                                                                                                            <br />
                                                                                                                                                                                                                                                                                                            <div id="sale_chart" class="chart"></div>

                                                                                                                                                                                                                                                                                                            <div class="map_canvas">

                                                                                                                                                                                                                                                                                                                        <canvas id="myChart" width="auto" height="100"></canvas>
                                                                                                                                                                                                                                                                                                            </div> -->
                <!-- CONTAINER END -->
            </div>
        </div>
    @endsection
    @section('page-scripts')
        <script>
            $(document).ready(function() {
                $('#sale_category').change(function() {
                    let category = $(this).val();
                    alert(category);
                    if (category) {
                        $.ajax({
                            url: `{{ route('admin.common.latest.sale', ':category') ?? 0 }}`.replace(
                                ':category', category),
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    console.log(response.data);
                                    response.data.forEach(function(sale) {

                                        salesHtml = `
                                    <div class="clearfix row mb-3 " >


                                        <div class="col">
                                            <div class="float-start">
                                                <h5 class="mb-0 fs-16"><strong>ITem Name</strong></h5>
                                                <small class="text-muted">Rate : 30 , Qty : 20</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="float-end">
                                            <h4 class="fw-bold fs-18 mb-0 mt-2 text-primary">Rs 600</h4>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                    });

                                    $('#latest-sale').html(salesHtml);
                                } else {
                                    salesHtml = `
                                    <div class="clearfix row mb-3 " >


                                        <div class="col">
                                            <div class="float-start">
                                                <h5 class="mb-0 fs-16"><strong>ITem Not Found</strong></h5>

                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="float-end">
                                            <h4 class="fw-bold fs-18 mb-0 mt-2 text-primary">00</h4>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                    $('#latest-sale').html(salesHtml);
                                }
                            },
                            error: function(xhr, status, error) {
                                $('#latest-sale').html('<p>An error occurred</p>');
                            }
                        });
                    } else {
                        $('#latest-sale').html('');
                    }
                });

                function fetchNearExpiryProducts(months) {
                    $.ajax({
                        url: "{{ route('admin.stock.near_expiry_products') }}",
                        type: 'GET',
                        data: {
                            months: months
                        },
                        success: function(data) {
                            var tbody = $('#expiredItemsTbody');
                            tbody.empty();
                            $.each(data, function(index, item) {
                                var row = `<tr>
                                   <td>${item.id}</td>
                                   <td>${item.name}</td>
                                   <td>${item.expiry_date}</td>
                                   <td style="text-align: right">${item.quantity}</td>
                               </tr>`;
                                tbody.append(row);
                            });
                        }
                    });
                }

                var initialMonths = $('#expiryDropdown').val();
                fetchNearExpiryProducts(initialMonths);

                $('#expiryDropdown').on('change', function() {
                    var months = $(this).val();
                    fetchNearExpiryProducts(months);
                });
            });
        </script>
    @endsection
