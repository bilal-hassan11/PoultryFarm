
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-aFq/bzH65dt+w6FI2ooMVUpc+21e0SRygnTpmBvdBgSdnuTN7QbdgL+OapgHtvPp" crossorigin="anonymous">

    <title>Ledger</title>
    <style>
       .challan_details {
    display: flex;
    /* align-items: center; */
    justify-content: space-between;
    margin: 25px 0px;
    width: 100%;
}
.bottom{
    margin-top: 100px;
}
table,th,td{
    border: 1px solid #000;
    padding: 4px;
    font-size: 12px;
}
p{
    font-size: 13px;
}
h3{
    margin-top: 50px;
    text-decoration: underline;
}
h4{
    margin-top: 15px;
}
h5{
    margin: 30px 0px;
    color: rgb(24, 94, 225);
    font-size: 18px;
}
.cr{
    color: rgb(24, 94, 225);
}
.dr{
    color: rgb(255, 229, 71);
}
.debit{
    color: rgb(60, 179, 113);
}
.credit{
    color: rgb(255, 0, 0);
}
.opening{
    color: rgb(106, 90, 205);
}
.ac{
    color: rgb(23, 194, 69);
}

    </style>
  </head>
  <body >
<div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="challan_wrapper">
                    <center>
                    <table style="border: none;">

                    <tr style="border: none;">
                        <td style="border: none;"></td>

                        <td style="border: none;">
                            <h3 class="text-center">Tawakkal Marketing Traders |{{@$data['title']}}</h3>
                            @if(@$data['account_name'] != "")
                           <h5 class="text-center ac"><i class="bi bi-people-fill"></i>{{ $data['account_name'][0]->name  }} </h5>
                           @endif
                           <h5 class="text-center ac">{{@$data['title']}}</h5>
                          <h5 class="text-center">From {{ @$data['from_date'] }} to {{ @$data['to_date'] }}</h5>


                        </td>
                    </tr>

                   </table>
                 </center>

                    <table  class="table table-bordered border-secondary" style="border-size:2px;" border="1">
                        <thead>
                            <tr class="text-dark">
                                <th> Date  </th>
                                <th> Account Name </th>
                                <th> Item Name </th>
                                <th> Quantity </th>
                                <th> Rate </th>
                                <th> Net Value </th>
                            </tr>
                        </thead>
                        <tbody>
                            @if( @$all_reports_values != "")
                                @foreach($data['all_reports_values'] AS $all)
                                <tr class="text-dark">
                                    <td><span class="waves-effect waves-light btn btn-rounded btn-primary-light">{{ date('d-m-y', strtotime(@$all->date)) }}</span></td>
                                    <td ><span class="waves-effect waves-light btn btn-outline btn-success">{{ @$all->account->name }}</span></td>
                                    <td><span class="waves-effect waves-light btn btn-outline btn-danger">{{ @$all->item->name }}</span></td>
                                    <td>{{    number_format(@$all->net_amount / @$all->quantity ,2) }}</td>
                                    <td>{{ @$all->quantity }}</td>
                                    <td ><span class="waves-effect waves-light btn btn-outline btn-success">{{ $all->net_ammount }}</span></td>
                                </tr>
                            @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="text-dark">
                                <td>Total</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </tbody>
                    </table>


                </div>
            </div>


        </div>


</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js" integrity="sha384-qKXV1j0HvMUeCBQ+QVp7JcfGl760yU08IQ+GpUo5hlbpg51QRiuqHAJz8+BrxE/N" crossorigin="anonymous"></script>

  </body>
  </html>

