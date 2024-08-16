<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\PurchaseBook;
use App\Models\AccountLedger;
use App\Models\Expense;
use App\Models\CashBook;
use App\Models\AccountType;
use Mpdf\Mpdf;
use App\Models\FeedInvoice;
use App\Models\ChickInvoice;
use App\Models\MedicineInvoice;
use App\Models\MurghiInvoice;


use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{

    public function item_stock_report(Request $req){

        if(isset($req->to_date)){

           $from_date = $req->from_date;
           $to_date = $req->to_date;

           $items = Item::select(
               'items.*',
               DB::raw("(SELECT SUM(sale_feed.quantity) FROM sale_feed WHERE items.id = sale_feed.item_id AND sale_feed.date BETWEEN ? AND ?) AS sale_feed_sum_quantity"),
               DB::raw("(SELECT SUM(purchase_feed.quantity) FROM purchase_feed WHERE items.id = purchase_feed.item_id AND purchase_feed.date BETWEEN ? AND ?) AS purchase_feed_sum_quantity"),
               DB::raw("(SELECT SUM(return_feed.quantity) FROM return_feed WHERE items.id = return_feed.item_id AND return_feed.date BETWEEN ? AND ?) AS return_feed_sum_quantity")

               )
               ->where('type', $type)
               ->addBinding($from_date, 'select')
               ->addBinding($to_date, 'select')
               ->addBinding($from_date, 'select')
               ->addBinding($to_date, 'select')
               ->where('category_id',3)
               ->get();

        }else{

           $from_date = date('Y-m-d');
           $to_date = date('Y-m-d');

           $items = Item::withSum('sale_feed', 'quantity')->withSum('purchase_feed', 'quantity')->withSum('return_feed', 'quantity')
           ->where('category_id',3)->get();

        }

        $data = array(
            'title' => 'Item Report',
            'items' => $items,
            'is_update'=> true,
            'from_date' => $from_date,
            'to_date' => $to_date,


        );

       return view('admin.report.item_stock_report')->with($data);

    }

    public function DayBookReport(Request $req){



        if(isset($req->from_date)){



            //get Opening
            $c_cash_credit  = CashBook::whereDate('entry_date', '<', $req->from_date)->sum('receipt_ammount');
            $c_cash_debit  = CashBook::whereDate('entry_date', '<', $req->from_date)->sum('payment_ammount');
            $c_ex  = Expense::whereDate('date', '<', $req->from_date)->sum('ammount');

            $c_open = 0;
            $ccc_net = $c_open + $c_cash_credit;

            $ex_cc = $c_ex + $c_cash_debit ;

            $c_net_c = $ccc_net - $ex_cc;

            //dd($c_net_c);
            //get Closing
            $cash_credit  = CashBook::whereDate('entry_date', '<=', $req->from_date)->sum('receipt_ammount');
            $cash_debit  = CashBook::whereDate('entry_date', '<=', $req->from_date)->sum('payment_ammount');
            $ex  = Expense::whereDate('date', '<=', $req->from_date)->sum('ammount');
            $day_exp = Expense::whereDate('date', '=', $req->from_date)->sum('ammount');
            //get Cashbook
            $c  = CashBook::whereDate('entry_date', '=', $req->from_date)->latest()->get();

            $next_open = $c_net_c;
            $net_credit = $cash_credit - $c_cash_credit;
            $net_debit = $cash_debit - $c_cash_debit;
            $net_ex = $ex - $c_ex;
            $net = $net_credit + $next_open;

            $net_c = $net - $net_debit;
            $ov = $net_c - $ex ;

            $data = array(
                'title' => 'DayBook Report',
                'account_opening' => $c_net_c,
                'account_closing' => $ov,
                'expense' => $ex,
                'day_exp' => $day_exp,
                'credit' => $net_credit,
                'debit'  => $net_debit,
                'cashbook'  => $c,
                'is_update' => true,
                'from_date' => $req->from_date ,
                'purchase_medicine'  => PurchaseMedicine::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'sale_medicine'     => SaleMedicine::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'return_medicine'   => ReturnMedicine::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'purchase_murghi'   => PurchaseMurghi::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'sale_murghi'       => SaleMurghi::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'purchase_chick'   => PurchaseChick::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'sale_chick'       => SaleChick::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'purchase_feed'   => PurchaseFeed::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'sale_feed'      => SaleFeed::with(['item','account'])->whereDate('date', $req->from_date)->latest()->get(),
                'return_feed' => ReturnFeed::with(['item', 'account'])->whereDate('date', $req->from_date)->latest()->get(),

                'cash'          => CashBook::with(['account'])->whereDate('entry_date', $req->from_date)->latest()->get(),


            );
           // dd($data['purchases']);
        }else{


            $current_month = date('Y-m-d');

            //Feed
            $tot_sale_feed = SaleFeed::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_purchase_feed = PurchaseFeed::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_return_feed = ReturnFeed::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();

            //Medicine
            $tot_sale_medicine = SaleMedicine::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_purchase_medicine = PurchaseMedicine::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_return_medicine = ReturnMedicine::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            //dd($tot_sale_medicine);

            //Chicks
            $tot_sale_chick = SaleChick::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_purchase_chick = PurchaseChick::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();


            //Murghi
            $tot_sale_murghi = SaleMurghi::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_purchase_murghi = PurchaseMurghi::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            //Cashflow
            //get Opening
            $c_cash_credit  = CashBook::sum('receipt_ammount');
            $c_cash_debit  = CashBook::sum('payment_ammount');
            $c_ex  = Expense::sum('ammount');

            $c_open = 2909858;
            $ccc_net = $c_open + $c_cash_credit;

            $ex_cc = $c_ex + $c_cash_debit ;

            $c_net_c = $ccc_net - $ex_cc;

            //dd($c_net_c);
            //get Closing
            $cash_credit  = CashBook::sum('receipt_ammount');
            $cash_debit  = CashBook::sum('payment_ammount');

            $ex  = Expense::sum('ammount');


            $day_exp = Expense::whereDate('date', '=', $current_month)->sum('ammount');

            //get Cashbook
            $c  = CashBook::whereDate('entry_date', '=', $current_month)->latest()->get();

            $next_open = $c_net_c;
            $net_credit = $cash_credit - $c_cash_credit;
            $net_debit = $cash_debit - $c_cash_debit;
            $net_ex = $ex - $c_ex;
            $net = $net_credit + $next_open;

            $net_c = $net - $net_debit;
            $ov = $net_c - $ex ;

            $data = array(
                'title' => 'DayBook report',
                'account_opening' => $c_net_c,
                'account_closing' => $ov,
                'expense' => $ex,
                'day_exp' => $day_exp,
                'credit' => $net_credit,
                'debit'  => $net_debit,
                'cashbook'  => $c,
                'date'      => $current_month,

                'purchase_medicine'  => $tot_purchase_medicine,
                'sale_medicine'     =>  $tot_sale_medicine,
                'return_medicine'     =>  $tot_return_medicine,

                'purchase_murghi'   => $tot_purchase_murghi,
                'sale_murghi'       => $tot_sale_murghi,
                'purchase_chick'   => $tot_purchase_chick,
                'sale_chick'       => $tot_sale_chick,
                'purchase_feed'   => $tot_purchase_feed,
                'sale_feed'       => $tot_sale_feed,
                'return_feed'       => $tot_return_feed,

            );
        }


        return view('admin.report.daybook_report')->with($data);
    }

    public function DayBookPdf(Request $req){

        if(isset($req->from_date)){

            //get Opening
            $c_cash_credit  = CashBook::whereDate('date', '<', $req->from_date)->sum('receipt_ammount');
            $c_cash_debit  = CashBook::whereDate('date', '<', $req->from_date)->sum('payment_ammount');
            $c_ex  = Expense::whereDate('date', '<', $req->from_date)->sum('ammount');

            $c_open = 2909858;
            $ccc_net = $c_open + $c_cash_credit;

            $ex_cc = $c_ex + $c_cash_debit ;

            $c_net_c = $ccc_net - $ex_cc;


            //get Closing
            $cash_credit  = CashBook::whereDate('date', '<=', $req->from_date)->sum('receipt_ammount');
            $cash_debit  = CashBook::whereDate('date', '<=', $req->from_date)->sum('payment_ammount');
            $ex  = Expense::whereDate('date', '<=', $req->from_date)->sum('ammount');
            $day_exp = Expense::whereDate('date', '=', $req->from_date)->sum('ammount');
            //get Cashbook
            $c  = CashBook::whereDate('date', '=', $req->from_date)->latest()->get();

            $next_open = $c_net_c;
            $net_credit = $cash_credit - $c_cash_credit;
            $net_debit = $cash_debit - $c_cash_debit;
            $net_ex = $ex - $c_ex;
            $net = $net_credit + $next_open;

            $net_c = $net - $net_debit;
            $ov = $net_c - $ex ;

            $data = array(
                'title' => 'DayBook Report',

                'account_opening' => $c_net_c,
                'account_closing' => $ov,
                'expense' => $ex,
                'day_exp' => $day_exp,
                'credit' => $net_credit,
                'debit'  => $net_debit,
                'cashbook'  => $c,

                'from_date' => $req->from_date ,
                'purchase_medicine'  => PurchaseMedicine::with(['item',                             'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'sale_medicine'     => SaleMedicine::with(['item',                             'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'return_medicine'   => ReturnMedicine::with(['item',                             'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'purchase_murghi'   => PurchaseMurghi::with(['item',                             'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'sale_murghi'       => SaleMurghi::with(['item',                             'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'purchase_chick'   => PurchaseChick::with(['item',                             'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'sale_chick'       => SaleChick::with(['item',                             'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'purchase_feed'   => PurchaseFeed::with(['item',                             'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'sale_feed'      => SaleFeed::with(['item',                                     'account'])->whereDate('date', $req                         ->from_date)->latest()->get(),
                'return_feed' => ReturnFeed::with(['item', 'account']                           )->whereDate('date', $req->from_date                        )->latest()->get(),

                'cash'          => CashBook::with(['account'])->whereDate                   ('date', $req->from_date)->latest()->get(),


            );
           //dd($data['purchase_medicine']);
        }else{


            $current_month = date('Y-m-d');

            //Feed
            $tot_sale_feed = SaleFeed::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_purchase_feed = PurchaseFeed::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_return_feed = ReturnFeed::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();

            //Medicine
            $tot_sale_medicine = SaleMedicine::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_purchase_medicine = PurchaseMedicine::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_return_medicine = ReturnMedicine::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            //dd($tot_sale_medicine);

            //Chicks
            $tot_sale_chick = SaleChick::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_purchase_chick = PurchaseChick::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();


            //Murghi
            $tot_sale_murghi = SaleMurghi::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            $tot_purchase_murghi = PurchaseMurghi::with(['item', 'account'])->whereDate('date', $current_month)->latest()->get();
            //Cashflow
            //get Opening
            $c_cash_credit  = CashBook::sum('receipt_ammount');
            $c_cash_debit  = CashBook::sum('payment_ammount');
            $c_ex  = Expense::sum('ammount');

            $c_open = 2909858;
            $ccc_net = $c_open + $c_cash_credit;

            $ex_cc = $c_ex + $c_cash_debit ;

            $c_net_c = $ccc_net - $ex_cc;

            //dd($c_net_c);
            //get Closing
            $cash_credit  = CashBook::sum('receipt_ammount');
            $cash_debit  = CashBook::sum('payment_ammount');

            $ex  = Expense::sum('ammount');


            $day_exp = Expense::whereDate('date', '=', $current_month)->sum('ammount');

            //get Cashbook
            $c  = CashBook::whereDate('date', '=', $current_month)->latest()->get();

            $next_open = $c_net_c;
            $net_credit = $cash_credit - $c_cash_credit;
            $net_debit = $cash_debit - $c_cash_debit;
            $net_ex = $ex - $c_ex;
            $net = $net_credit + $next_open;

            $net_c = $net - $net_debit;
            $ov = $net_c - $ex ;

            $data = array(
                'title' => 'DayBook report',
                'account_opening' => $c_net_c,
                'account_closing' => $ov,
                'expense' => $ex,
                'day_exp' => $day_exp,
                'credit' => $net_credit,
                'debit'  => $net_debit,
                'cashbook'  => $c,
                'date'      => $current_month,

                'purchase_medicine'  => $tot_purchase_medicine,
                'sale_medicine'     =>  $tot_sale_medicine,
                'return_medicine'     =>  $tot_return_medicine,

                'purchase_murghi'   => $tot_purchase_murghi,
                'sale_murghi'       => $tot_sale_murghi,
                'purchase_chick'   => $tot_purchase_chick,
                'sale_chick'       => $tot_sale_chick,
                'purchase_feed'   => $tot_purchase_feed,
                'sale_feed'       => $tot_sale_feed,
                'return_feed'       => $tot_return_feed,

            );
        }

        $pdf = Pdf::loadView('admin.report.daybook_report_pdf', $data);
        return $pdf->download('daybook_report_pdf.pdf');

    }

    public function cashflowReport(Request $req){


        if(isset($req->to_date)){

            //get Opening
            $c_cash_credit  = CashBook::whereDate('entry_date', '<', $req->to_date)->sum('receipt_ammount');
            $c_cash_debit  = CashBook::whereDate('entry_date', '<', $req->to_date)->sum('payment_ammount');
            $c_ex  = Expense::whereDate('date', '<', $req->to_date)->sum('ammount');

            $c_open = 0;
            $ccc_net = $c_open + $c_cash_credit;

            $ex_cc = $c_ex + $c_cash_debit ;

            $c_net_c = $ccc_net - $ex_cc;

            //dd($c_net_c);
            //get Closing
            $cash_credit  = CashBook::whereDate('entry_date', '<=', $req->to_date)->sum('receipt_ammount');
            $cash_debit  = CashBook::whereDate('entry_date', '<=', $req->to_date)->sum('payment_ammount');
            $ex  = Expense::whereDate('date', '<=', $req->to_date)->sum('ammount');
            $day_exp = Expense::whereDate('date', '=', $req->to_date)->sum('ammount');
            //get Cashbook
            $c  = CashBook::whereDate('entry_date', '=', $req->to_date)->latest()->get();

            $next_open = $c_net_c;
            $net_credit = $cash_credit - $c_cash_credit;
            $net_debit = $cash_debit - $c_cash_debit;
            $net_ex = $ex - $c_ex;
            $net = $net_credit + $next_open;

            $net_c = $net - $net_debit;
            $ov = $net_c - $ex ;
            //dd($net_c);
            // dd($net_c);
            // dd($cash_credit);



            $data = array(
                'title' => 'CashFlow Report',
                'account_opening' => $c_net_c,
                'account_closing' => $ov,
                'expense' => $ex,
                'day_exp' => $day_exp,
                'credit' => $net_credit,
                'debit'  => $net_debit,


                'to_date' => $req->to_date ,
                'cashbook'  => $c
            );
            //dd($data['cashbook']);

        }else{

            $data = array(
                'title' => 'CashFlow Report',


            );
        }

        return view('admin.report.cashflow')->with($data);
    }

    public function cashflowReportPdf(Request $req){


        if(isset($req->to_date)){

            //get Opening
            $c_cash_credit  = CashBook::whereDate('entry_date', '<', $req->to_date)->sum('receipt_ammount');
            $c_cash_debit  = CashBook::whereDate('entry_date', '<', $req->to_date)->sum('payment_ammount');
            $c_ex  = Expense::whereDate('date', '<', $req->to_date)->sum('ammount');

            $c_open = 0;
            $ccc_net = $c_open + $c_cash_credit;

            $ex_cc = $c_ex + $c_cash_debit ;

            $c_net_c = $ccc_net - $ex_cc;

            //dd($c_net_c);
            //get Closing
            $cash_credit  = CashBook::whereDate('entry_date', '<=', $req->to_date)->sum('receipt_ammount');
            $cash_debit  = CashBook::whereDate('entry_date', '<=', $req->to_date)->sum('payment_ammount');
            $ex  = Expense::whereDate('date', '<=', $req->to_date)->sum('ammount');
            $day_exp = Expense::whereDate('date', '=', $req->to_date)->sum('ammount');
            //get Cashbook
            $c  = CashBook::whereDate('entry_date', '=', $req->to_date)->latest()->get();

            $next_open = $c_net_c;
            $net_credit = $cash_credit - $c_cash_credit;
            $net_debit = $cash_debit - $c_cash_debit;
            $net_ex = $ex - $c_ex;
            $net = $net_credit + $next_open;

            $net_c = $net - $net_debit;
            $ov = $net_c - $ex ;
            //dd($net_c);
            // dd($net_c);
            // dd($cash_credit);



            $data = array(
                'title' => 'CashFlow Report',
                'account_opening' => $c_net_c,
                'account_closing' => $ov,
                'expense' => $ex,
                'day_exp' => $day_exp,
                'credit' => $net_credit,
                'debit'  => $net_debit,


                'to_date' => $req->to_date ,
                'cashbook'  => $c
            );
            //dd($data['cashbook']);

        }else{

            $data = array(
                'title' => 'CashFlow Report',


            );
        }

        $pdf = Pdf::loadView('admin.report.cashflow_pdf', $data);
        return $pdf->download('CashFlow.pdf');

    }

    public function accounts_head_report(Request $req){


        if(isset($req->from_date) ){


            $accounts = Account::where('grand_parent_id','=',hashids_decode($req->parent_id))->latest()->get();
            //dd($accounts);

            for($i = 0; $i < count($accounts); $i++) {


                $arr = [];
                $balance = $accounts[$i]->opening_balance;
                $t_cr = AccountLedger::where('account_id',$accounts[$i]->id)->whereDate('date', '<=', $req->from_date)->sum('credit');
                $t_dr = AccountLedger::where('account_id',$accounts[$i]->id)->whereDate('date', '<=', $req->from_date)->sum('debit');


                if($accounts[$i]->account_nature == "credit"){
                    $t_cr += $balance;

                }else{

                    $t_dr +=  $balance;
                }

                $dues = $t_cr - $t_dr;

                if($dues < 0){
                    $a_n = "debit";

                }else{

                    $a_n = "credit";
                }

                $accounts[$i]->opening_balance = $dues;
                $accounts[$i]->account_nature = $a_n;

            }
            //dd($accounts);
            $data = array(
                'title' => 'All Accounts Ledger',

                'Item' => Item::where('category_id',3)->latest()->get(),
                'acounts' => Account::latest()->get(),
                'ac' => $accounts ,
                'account_types' => AccountType::whereNull('parent_id')->get(),
                'accounts'  => Account::latest()->get(),

            );
        }else{


            $accounts = Account::latest()->get();


            for($i = 0; $i < count($accounts); $i++) {

                $arr = [];
                $balance = $accounts[$i]->opening_balance;
                $t_cr = AccountLedger::where('account_id',$accounts[$i]->id)->sum('credit');
                $t_dr = AccountLedger::where('account_id',$accounts[$i]->id)->sum('debit');


                if($accounts[$i]->account_nature == "credit"){
                    $t_cr += $balance;

                }else{

                    $t_dr +=  $balance;
                }

                $dues = $t_cr - $t_dr;

                if($dues < 0){
                    $a_n = "debit";

                }else{

                    $a_n = "credit";
                }

                $accounts[$i]->opening_balance = $dues;
                $accounts[$i]->account_nature = $a_n;

            }
            //dd($accounts);
            $data = array(
                'title' => 'All Accounts Ledger',

                'Item' => Item::where('category_id',3)->latest()->get(),
                'acounts' => Account::latest()->get(),
                'ac' => $accounts ,
                'account_types' => AccountType::whereNull('parent_id')->get(),
                'accounts'  => Account::latest()->get(),

            );
        }

        return view('admin.report.accounts_head_report')->with($data);
    }

    public function all_accounts_report_request(Request $req){

        if(isset($req->from_date) ){


            $accounts = Account::where('grand_parent_id','=',hashids_decode($req->parent_id))->latest()->get();
            //dd($accounts);

            for($i = 0; $i < count($accounts); $i++) {


                $arr = [];
                $balance = $accounts[$i]->opening_balance;
                $t_cr = AccountLedger::where('account_id',$accounts[$i]->id)->whereDate('date', '<=', $req->from_date)->sum('credit');
                $t_dr = AccountLedger::where('account_id',$accounts[$i]->id)->whereDate('date', '<=', $req->from_date)->sum('debit');


                if($accounts[$i]->account_nature == "credit"){
                    $t_cr += $balance;

                }else{

                    $t_dr +=  $balance;
                }

                $dues = $t_cr - $t_dr;

                if($dues < 0){
                    $a_n = "debit";

                }else{

                    $a_n = "credit";
                }

                $accounts[$i]->opening_balance = $dues;
                $accounts[$i]->account_nature = $a_n;

            }
            $data = array(
                'title' => 'All Accounts Report',
                'ac' => $accounts ,
                'account_types' => AccountType::whereNull('parent_id')->get(),

            );

        }else{


            $accounts = Account::latest()->get();


            for($i = 0; $i < count($accounts); $i++) {

                $arr = [];
                $balance = $accounts[$i]->opening_balance;
                $t_cr = AccountLedger::where('account_id',$accounts[$i]->id)->sum('credit');
                $t_dr = AccountLedger::where('account_id',$accounts[$i]->id)->sum('debit');


                if($accounts[$i]->account_nature == "credit"){
                    $t_cr += $balance;

                }else{

                    $t_dr +=  $balance;
                }

                $dues = $t_cr - $t_dr;

                if($dues < 0){
                    $a_n = "debit";

                }else{

                    $a_n = "credit";
                }

                $accounts[$i]->opening_balance = $dues;
                $accounts[$i]->account_nature = $a_n;

            }

            $data = array(
                'title' => 'All Accounts Report',
                'ac' => $accounts ,
                'account_types' => AccountType::whereNull('parent_id')->get(),

            );
        }

        return view('admin.report.all_accounts_reports')->with($data);
    }

    public function all_report($id){

        //dd($id);
        if($id == "purchase_medicine"){

            $data = array(
                'title' => 'Purchase Medicine Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',4)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "purchasemedicine",
                'all_reports_values' => MedicineInvoice::with('item','account')->where('type','Purchase')
                                        ->latest()->get(),


            );

        }

        //Sale Medicine
        if($id == "sale_medicine"){

            $data = array(
                    'title' => 'Sale Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::where('category_id',4)->latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salemedicine",
                    'all_reports_values' => MedicineInvoice::with('item','account')->where('type','Sale')
                                            ->latest()->get(),


                );

        }

        //Purchase Return Medicine
        if($id == "purchase_return"){

            $data = array(
                'title' => 'Purchase Return Medicine Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',4)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "purchasereturnmedicine",
                'all_reports_values' => MedicineInvoice::with('item','account')->where('type','Purchase Return')
                                        ->latest()->get(),


            );

        }

        //Sale Return Medicine
        if($id == "sale_return"){

            $data = array(
                'title' => 'Sale Return Medicine Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',4)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "salereturnmedicine",
                'all_reports_values' => MedicineInvoice::with('item','account')->where('type','Sale Return')
                                        ->latest()->get(),


            );

        }


        //Adjust In Medicine
        if($id == "medicine_adjust_in"){

            $data = array(
                'title' => 'Adjust In Medicine Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',4)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "adjustinmedicine",
                'all_reports_values' => MedicineInvoice::with('item','account')->where('type','Adjust In')
                                        ->latest()->get(),


            );

        }

        //Adjust Out Medicine
        if($id == "medicine_adjust_out"){

            $data = array(
                'title' => 'Adjust Out Medicine Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',4)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "adjustoutmedicine",
                'all_reports_values' => MedicineInvoice::with('item','account')->where('type','Adjust Out')
                                        ->latest()->get(),


            );

        }

        //Purchase Feed
        if($id == "purchase_feed"){

            $data = array(
                'title' => 'Purchase Feed Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',3)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "purchasefeed",
                'all_reports_values' => FeedInvoice::with('item','account')->where('type','Purchase')
                                            ->latest()->get(),


            );

        }

        //Sale Feed
        if($id == "sale_feed"){

            $data = array(
                    'title' => 'Sale Feed Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::where('category_id',3)->latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salefeed",
                    'all_reports_values' => FeedInvoice::with('item','account')->where('type','Sale')
                                            ->latest()->get(),


                );

        }

        //Purchase Return Feed
        if($id == "purchase_return_feed"){

            $data = array(
                'title' => 'Purchase Return Feed Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',3)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "purchasereturnfeed",
                'all_reports_values' => FeedInvoice::with('item','account')->where('type','Purchase Return')
                                            ->latest()->get(),


            );

        }

        //Sale Return Feed
        if($id == "sale_return_feed"){

            $data = array(
                    'title' => 'Sale Return Feed Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::where('category_id',3)->latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salereturnfeed",
                    'all_reports_values' => FeedInvoice::with('item','account')->where('type','Sale Return')
                                            ->latest()->get(),


                );

        }

        //Sale Chick
        if($id == "sale_chick"){

            $data = array(
                    'title' => 'Sale Chick Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::where('category_id',2)->latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salechick",
                    'all_reports_values' => ChickInvoice::with('item','account')->where('type','Purchase')
                                            ->latest()->get(),


                );

        }

        //Purchase Chick
        if($id == "purchase_chick"){

            $data = array(
                'title' => 'Purchase Chick Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',2)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "purchasechick",
                'all_reports_values' => ChickInvoice::with('item','account')->where('type','Sale')
                                        ->latest()->get(),


            );

        }

        //Sale Murghi
        if($id == "sale_murghi"){

            $data = array(
                    'title' => 'Sale Murghi Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::where('category_id',8)->latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salemurghi",
                    'all_reports_values' => MurghiInvoice::with('item','account')->where('type','Purchase')
                                            ->latest()->get(),


                );

        }

        //Purchase Murghi
        if($id == "Purchase_murghi"){

            $data = array(
                'title' => 'Purchase Murghi Report',
                'acounts' => Account::latest()->get(),
                'items' => Item::where('category_id',8)->latest()->get(),
                'accounts'  => Account::latest()->get(),
                'item_name' => false,
                'account_name' => false,
                'id'  => "purchasemurghi",
                'all_reports_values' => MurghiInvoice::with('item','account')->where('type','Sale')
                                        ->latest()->get(),


            );

        }

        return view('admin.report.all_reports')->with($data);
    }

    public function all_reports_request(Request $req){

        //Purchase Medicine
        if($req->id == "purchasemedicine"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );


                }

                if(isset($req->account_id) && isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){

                    $data = array(
                        'title' => 'Purchase Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){
                    $data = array(
                        'title' => 'Purchase Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }


            }else{
                $data = array(
                    'title' => 'Purchase Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasemedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Medicine
        if($req->id == "salemedicine"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salemedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Return Medicine
        if($req->id == "purchasereturnmedicine"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Return Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );


                }

                if(isset($req->account_id) && isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Return Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){

                    $data = array(
                        'title' => 'Purchase Return Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){
                    $data = array(
                        'title' => 'Purchase Return Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }


            }else{
                $data = array(
                    'title' => 'Purchase Return Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasereturnmedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Return Medicine
        if($req->id == "salereturnmedicine"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Return Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Return Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Return Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Return Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Return Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salereturnmedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Adjust In Medicine
         if($req->id == "adjustinmedicine"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){
                    $data = array(
                        'title' => 'Adjust In Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustinmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust In')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );


                }

                if(isset($req->account_id) && isset($req->item_id)){
                    $data = array(
                        'title' => 'Adjust In Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustinmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust In')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){

                    $data = array(
                        'title' => 'Adjust In Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustinmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust In')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){
                    $data = array(
                        'title' => 'Adjust In Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "adjustinmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust In')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }


            }else{
                $data = array(
                    'title' => 'Adjust In Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "adjustinmedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Adjust Out Medicine
        if($req->id == "adjustoutmedicine"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Adjust Out Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustoutmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust Out')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Adjust Out Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustoutmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust Out')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Adjust Out Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustoutmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust Out')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Adjust Out Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "adjustoutmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust Out')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Adjust Out Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "adjustoutmedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Feed
        if($req->id == "purchasefeed"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(!isset($req->account_id) && !isset($req->item_id) ){
                    $data = array(
                        'title' => 'Purchase Feed Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){

                    $data = array(
                        'title' => 'Purchase Feed Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && !isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );


                }

                if(isset($req->account_id) && isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }




                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Purchase Feed Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasefeed",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Medicine
        if($req->id == "salefeed"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salemedicine",
                        'all_reports_values'  => FeedInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Feed Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){
                    //dd($req->all());
                    $data = array(
                        'title' => 'Sale Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale')->with(['company','account','item'])->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->invoice_no), function($query) use ($req){
                                                        $query->where('invoice_no',$req->invoice_no);
                                                    })
                                                    ->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id',hashids_decode($req->item_id));
                                                    })
                                                    ->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }


                //dd($data);
            }else{
                $data = array(
                    'title' => 'Sale Feed Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salefeed",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Return Feed
        if($req->id == "purchasereturnfeed"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Return Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Return Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Purchase Return Feed Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Purchase Return Feed Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Purchase Return Feed Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasereturnfeed",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Return Feed
        if($req->id == "salereturnfeed"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Return Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Return Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Return Feed Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Return Feed Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Return Feed Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salereturnfeed",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Chcik
        if($req->id == "salechick"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Chick Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salechick",
                        'all_reports_values'  => ChickInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Chick Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salechick",
                        'all_reports_values'  => ChickInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Chick Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salechick",
                        'all_reports_values'  => ChickInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Feed Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salechick",
                        'all_reports_values'  => ChickInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Chcik Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salechick",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Chick
        if($req->id == "purchasechick"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Chick Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasechick",
                        'all_reports_values'  => ChickInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Chick Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasechick",
                        'all_reports_values'  => ChickInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Purchase Chick Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasechick",
                        'all_reports_values'  => ChickInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Purchase Chick Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasechick",
                        'all_reports_values'  => ChickInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Purchase Chick Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasechick",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Murghi
        if($req->id == "salemurghi"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Murghi Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Murghi Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Murghi Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Murghi Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Murghi Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salemurghi",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Murghi
        if($req->id == "purchasemurghi"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Murghi Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Murghi Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Purchase Murghi Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Purchase Murghi Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Purchase Murghi Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasemurghi",
                    'all_reports_values' => "",


                );
            }
        }

        return view('admin.report.all_reports')->with($data);
    }

    public function all_reports_pdf(Request $req){

        //dd($req->all());
        if($req->id == "purchasemedicine"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => "",

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );


                }

                if(isset($req->account_id) && isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){

                    $data = array(
                        'title' => 'Purchase Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){
                    $data = array(
                        'title' => 'Purchase Medicine Report',
                        'account_name' => false,

                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Purchase Medicine Report',

                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasemedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Medicine
        if($req->id == "salemedicine"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => "",

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => false,

                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salemedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Medicine Report',

                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salemedicine",
                    'all_reports_values' => "",


                );
            }
        }

       //Purchase Return Medicine
        if($req->id == "purchasereturnmedicine"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Return Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );


                }

                if(isset($req->account_id) && isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Return Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){

                    $data = array(
                        'title' => 'Purchase Return Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){
                    $data = array(
                        'title' => 'Purchase Return Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }


            }else{
                $data = array(
                    'title' => 'Purchase Return Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasereturnmedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Return Medicine
        if($req->id == "salereturnmedicine"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Return Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Return Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Return Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Return Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salereturnmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Return Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salereturnmedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Adjust In Medicine
        if($req->id == "adjustinmedicine"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){
                    $data = array(
                        'title' => 'Adjust In Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustinmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust In')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );


                }

                if(isset($req->account_id) && isset($req->item_id)){
                    $data = array(
                        'title' => 'Adjust In Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustinmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust In')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){

                    $data = array(
                        'title' => 'Adjust In Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustinmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust In')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){
                    $data = array(
                        'title' => 'Adjust In Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "adjustinmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust In')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }


            }else{
                $data = array(
                    'title' => 'Adjust In Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "adjustinmedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Adjust Out Medicine
        if($req->id == "adjustoutmedicine"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Adjust Out Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustoutmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust Out')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Adjust Out Medicine Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustoutmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust Out')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Adjust Out Medicine Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "adjustoutmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust Out')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Adjust Out Medicine Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "adjustoutmedicine",
                        'all_reports_values'  => MedicineInvoice::where('type','Adjust Out')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Adjust Out Medicine Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "adjustoutmedicine",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Feed
        if($req->id == "purchasefeed"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(!isset($req->account_id) && !isset($req->item_id) ){
                    $data = array(
                        'title' => 'Purchase Feed Report',
                        'account_name' => false,

                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){

                    $data = array(
                        'title' => 'Purchase Feed Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && !isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasefeed",
                        'all_reports_values'  => PurchaseFeed::when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );


                }

                if(isset($req->account_id) && isset($req->item_id)){
                    $data = array(
                        'title' => 'Purchase Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

            }else{
                $data = array(
                    'title' => 'Purchase Feed Report',

                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasefeed",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Feed
        if($req->id == "salefeed"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Medicine Report',
                        'account_name' => false,

                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salemedicine",
                        'all_reports_values'  => FeedInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Feed Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => "",

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){
                    //dd($req->all());
                    $data = array(
                        'title' => 'Sale Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salefeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale')->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->invoice_no), function($query) use ($req){
                                                        $query->where('invoice_no',$req->invoice_no);
                                                    })
                                                    ->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id',hashids_decode($req->item_id));
                                                    })
                                                    ->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }


                //dd($data);
            }else{
                $data = array(
                    'title' => 'Sale Feed Report',

                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salefeed",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Return Feed
        if($req->id == "purchasereturnfeed"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Return Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Return Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Purchase Return Feed Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Purchase Return Feed Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Purchase Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Purchase Return Feed Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasereturnfeed",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Return Feed
        if($req->id == "salereturnfeed"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Return Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => "",
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Return Feed Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Return Feed Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Return Feed Report',
                        'account_name' => false,
                        'acounts' => Account::latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salereturnfeed",
                        'all_reports_values'  => FeedInvoice::where('type','Sale Return')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Return Feed Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salereturnfeed",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Chick
        if($req->id == "salechick"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Chick Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => "",

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salechick",
                        'all_reports_values'  => ChickInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Chick Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salechick",
                        'all_reports_values'  => ChickInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Chick Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salechick",
                        'all_reports_values'  => ChickInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Feed Report',
                        'account_name' => false,

                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salechick",
                        'all_reports_values'  => ChickInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Chcik Report',


                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salechick",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Chick
        if($req->id == "purchasechick"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Chick Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => "",

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasechick",
                        'all_reports_values'  => ChickInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Chick Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasechick",
                        'all_reports_values'  => PurchaseChick::when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Purchase Chick Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasechick",
                        'all_reports_values'  => PurchaseChick::when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Purchase Chick Report',
                        'account_name' => false,

                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasechick",
                        'all_reports_values'  => PurchaseChick::when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Purchase Chick Report',

                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasechick",
                    'all_reports_values' => "",


                );
            }
        }

        //Sale Murghi
        if($req->id == "salemurghi"){
            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Murghi Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => "",

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Sale Murghi Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),

                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Sale Murghi Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),

                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "salemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Sale Murghi Report',
                        'account_name' => false,

                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "salemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Sale')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                //dd($data['purchases']);
            }else{
                $data = array(
                    'title' => 'Sale Murghi Report',

                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "salemurghi",
                    'all_reports_values' => "",


                );
            }
        }

        //Purchase Murghi
        if($req->id == "purchasemurghi"){

            if(isset($req->account_id) || isset($req->item_id) || isset($req->to_date)){

                if(isset($req->account_id) && !isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Murghi Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'item_name' => "",
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->account_id) && isset($req->item_id)){

                    $data = array(
                        'title' => 'Purchase Murghi Report',
                        'account_name' => Account::where('id',hashids_decode($req->account_id))->latest()->get(),
                        'acounts' => Account::latest()->get(),
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),
                        'items' =>   Item::latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(isset($req->item_id) && !isset($req->account_id)){


                    $data = array(
                        'title' => 'Purchase Murghi Report',
                        'account_name' => "",
                        'item_name' => Item::where('id',hashids_decode($req->item_id))->latest()->get(),


                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'is_update' => true,
                        'id'  => "purchasemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }

                if(!isset($req->account_id) && !isset($req->item_id) ){

                    $data = array(
                        'title' => 'Purchase Murghi Report',
                        'account_name' => false,

                        'item_name' => false,
                        'is_update' => true,
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'id'  => "purchasemurghi",
                        'all_reports_values'  => MurghiInvoice::where('type','Purchase')->when(isset($req->item_id), function($query) use ($req){
                                                        $query->where('item_id', hashids_decode($req->item_id));
                                                    })->when(isset($req->account_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->account_id));
                                                    })->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                                    })
                                                    ->orderBy('date', 'asc')->get(),
                    );

                }


            }else{
                $data = array(
                    'title' => 'Purchase Murghi Report',
                    'acounts' => Account::latest()->get(),
                    'items' => Item::latest()->get(),
                    'accounts'  => Account::latest()->get(),
                    'item_name' => false,
                    'account_name' => false,
                    'id'  => "purchasemurghi",
                    'all_reports_values' => "",


                );
            }
        }

        
        $html = view('admin.report.all_reports_pdf', compact('data'))->render();
        $mpdf = new Mpdf([
            'format' => 'A4-P', 'margin_top' => 10,
            'margin_bottom' => 2,
            'margin_left' => 2,
            'margin_right' => 2,
        ]);
        $mpdf->SetAutoPageBreak(true, 15);
        $mpdf->SetHTMLFooter('<div style="text-align: right;">Page {PAGENO} of {nbpg}</div>');
        return generatePDFResponse($html, $mpdf);

        // $pdf = Pdf::loadView('admin.report.all_reports_pdf', $data);
        // return $pdf->setPaper('a4')->stream();
    }

    public function accountReport(Request $req){


        if(isset($req->parent_id)){

                if( hashids_decode($req->parent_id) == 535 ){

                    $account_detail = Account::with(['grand_parent'])->where('id',hashids_decode($req->parent_id))->latest()->get();

                    if($account_detail[0]->account_nature == "debit" ){
                        $detail = "Assets";

                    }else{
                        $detail = "Not Assets";

                    }

                    $data = array(
                        'title' => 'Account Report',
                        'account_types' => AccountType::whereNull('parent_id')->get(),
                        'accounts'  => Account::latest()->get(),
                        'account_opening' => $account_detail ,
                        'account_parent' => $detail ,

                        'party_name' => Account::where('id',hashids_decode($req->parent_id))->latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,
                        'cash_in_hand' => true,
                        'account_ledger'  => AccountLedger::when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                                                        $query->whereDate('date', '>=', $req->from_date)->whereDate('date', '<=', $req->to_date);
                                                    })->where('cash_id','!=',0)->orderby('date','asc')->latest()->get()
                    );

                }else{

                    $account_detail = Account::with(['grand_parent'])->where('id',hashids_decode($req->parent_id))->latest()->get();

                    if($account_detail[0]->account_nature == "debit" ){
                        $detail = "Assets";

                    }else{
                        $detail = "Not Assets";

                    }


                    if($account_detail[0]->account_nature == "debit"){

                        $open_credit = AccountLedger::where('account_id',hashids_decode($req->parent_id))->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                            $query->whereDate('date', '<', $req->from_date);
                        })->where('credit' ,'!=',0)->sum('credit');
                        $open_debit = AccountLedger::where('account_id',hashids_decode($req->parent_id))->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                            $query->whereDate('date', '<', $req->from_date);
                        })->where('debit' ,'!=',0)->sum('debit');

                        $grand_open = ($account_detail[0]->opening_balance + $open_debit) - $open_credit;





                    }else{

                        $open_credit = AccountLedger::where('account_id',hashids_decode($req->parent_id))->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                            $query->whereDate('date', '<', $req->from_date);
                        })->where('credit' ,'!=',0)->sum('credit');
                        $open_debit = AccountLedger::where('account_id',hashids_decode($req->parent_id))->  when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                            $query->whereDate('date', '<', $req->from_date);
                        })->where('debit' ,'!=',0)->sum('debit');

                        $grand_open = ($account_detail[0]->opening_balance + $open_debit) - $open_credit;

                        //dd($open_credit);
                        //$grand_open = ($account_detail[0]->opening_balance + $open_credit) - $open_debit;
                    }

                    $account_detail[0]->opening_balance = abs($grand_open) ;
                    //dd($account_detail);

                    $data = array(
                        'title' => 'Account Report',
                        'account_types' => AccountType::whereNull('parent_id')->get(),
                        'accounts'  => Account::latest()->get(),
                        'account_opening' => $account_detail ,
                        'account_parent' => $detail ,
                        'cash_in_hand' => false,
                        'party_name' => Account::where('id',hashids_decode($req->parent_id))->latest()->get(),
                        'from_date' => $req->from_date ,
                        'to_date' => $req->to_date ,

                        'account_ledger'  => AccountLedger::when(isset($req->parent_id), function($query) use ($req){
                                                        $query->where('account_id', hashids_decode($req->parent_id));
                                                    })->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                                                        $query->whereDate('date', '>=', $req->from_date)->whereDate('date', '<=', $req->to_date);
                                                    })->orderBy('date','asc')->get()
                    );
                }
            //dd($data['party_name']);
        }else{

            $data = array(
                'title' => 'Account report',
                'acounts' => Account::latest()->get(),
                'account_types' => AccountType::whereNull('parent_id')->get(),
                'accounts'  => Account::latest()->get(),

            );
        }

        return view('admin.report.account_report')->with($data);
    }

    public function accountReportPdf(Request $req){
            //dd($req->from_date);
            $toDate = Carbon::parse($req->from_date);
            $fromDate = Carbon::parse($req->to_date);

            $days = $fromDate->diffInDays($toDate);
            $account_detail = Account::with(['grand_parent'])->where('id',hashids_decode($req->parent_id))->latest()->get();
             $names = $account_detail[0]->name . $days . 'Days' ;
            $data = array(
                'account_ledger'  => AccountLedger::when(isset($req->parent_id), function($query) use ($req){
                                                $query->where('account_id', hashids_decode($req->parent_id));
                                            })->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                                                $query->whereDate('date', '>=', $req->from_date)->whereDate('date', '<=', $req->to_date);
                                            })->orderBy('date', 'asc')->get(),
                'to_date' => $req->to_date,
                'from_date' => $req->from_date,
                'days' => $days,
                'names' =>  $names ,
                'account_opening' => $account_detail ,
                'account_name' =>  Account::findOrFail(hashids_decode($req->parent_id)),

            );






            $account_detail = Account::with(['grand_parent'])->where('id',hashids_decode($req->parent_id))->latest()->get();

            if($account_detail[0]->account_nature == "debit" ){
                $detail = "Assets";

            }else{
                $detail = "Not Assets";

            }


            if($account_detail[0]->account_nature == "debit"){

                $open_credit = AccountLedger::where('account_id',hashids_decode($req->parent_id))->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                    $query->whereDate('date', '<', $req->from_date);
                })->where('credit' ,'!=',0)->sum('credit');
                $open_debit = AccountLedger::where('account_id',hashids_decode($req->parent_id))->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                    $query->whereDate('date', '<', $req->from_date);
                })->where('debit' ,'!=',0)->sum('debit');

                $grand_open = ($account_detail[0]->opening_balance + $open_debit) - $open_credit;





            }else{

                $open_credit = AccountLedger::where('account_id',hashids_decode($req->parent_id))->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                    $query->whereDate('date', '<', $req->from_date);
                })->where('credit' ,'!=',0)->sum('credit');
                $open_debit = AccountLedger::where('account_id',hashids_decode($req->parent_id))->  when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                    $query->whereDate('date', '<', $req->from_date);
                })->where('debit' ,'!=',0)->sum('debit');

                $grand_open = ($account_detail[0]->opening_balance + $open_debit) - $open_credit;

                //dd($open_credit);
                //$grand_open = ($account_detail[0]->opening_balance + $open_credit) - $open_debit;
            }

            $account_detail[0]->opening_balance = abs($grand_open) ;

            $data = array(
                'title' => 'Account Report',
                'account_types' => AccountType::whereNull('parent_id')->get(),
                'accounts'  => Account::latest()->get(),
                'account_opening' => $account_detail ,
                'account_parent' => $detail ,
                'cash_in_hand' => false,
                'party_name' => Account::where('id',hashids_decode($req->parent_id))->latest()->get(),
                'to_date' => $req->to_date,
                'from_date' => $req->from_date,
                'days' => $days,
                'names' =>  $names ,
                'account_opening' => $account_detail ,
                'account_name' =>  Account::findOrFail(hashids_decode($req->parent_id)),
                'account_ledger'  => AccountLedger::when(isset($req->parent_id), function($query) use ($req){
                                                $query->where('account_id', hashids_decode($req->parent_id));
                                            })->when(isset($req->from_date) && isset($req->to_date), function($query) use ($req){
                                                $query->whereDate('date', '>=', $req->from_date)->whereDate('date', '<=', $req->to_date);
                                            })->orderBy('date','asc')->get()
            );


            $pdf = Pdf::loadView('admin.report.account_pdf', $data);
            return $pdf->download('.'.$names.'..pdf');
    }

}
