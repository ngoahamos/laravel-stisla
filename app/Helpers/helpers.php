<?php

use App\Contracts\TransactionType;
use App\Models\Loan;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('active_menu_class')) {

    function active_menu_class(string $menu): string
    {
        $path = explode('/', request()->path());
        $init = count($path) > 0 ? $path[0] : '';

        return $init == $menu ? 'active' : '';

    }
}

if (! function_exists('boolean_to_int')) {

    /**
     * @param $var
     * @return int
     */
    function boolean_to_int($var)
    {
        return ($var === 'true' ||
            $var === true   ||
            $var === '1'    ||
            $var === 1      ||
            $var === TRUE   ||
            $var === 'TRUE' ||
            $var === 'True')
            ? 1:0;
    }
}

if (! function_exists('image_placeholder')) {
    function image_placeholder($size = '150', $background = 'dddddd', $text = '', $color= "ffffff"){
        $size = empty($size) ? '150' : $size;
        $background = empty($background) ? 'dddddd' : $background;
        $text = empty($text) ? '' : $text;
        $color = empty($color) ? 'ffffff' : $color;

        $text = str_replace(" ", "+", $text);

        return "https://via.placeholder.com/$size/$background/$color?text=$text";
    }
}

if (! function_exists('str_first_character')) {
    function str_first_character($text = ''){
        if (strlen($text) < 4)
        {
            return $text;
        }
        $_text = array_map(function ($value) {
            return strtoupper(substr($value, 0,1));
        }, explode(' ', $text));

        return implode("", $_text);

    }
}

if (! function_exists('get_api_temp_base_ur')) {
    function get_api_temp_base_ur(): string {
        return 'https://api.susugh.com/';
    }
}

if (! function_exists('prepareResponse')) {

    function prepareResponse($status, $payload, $statusCode = Response::HTTP_OK){

        $res = new stdClass();
        $res->status = $status;
        $res->statusCode = $statusCode;

        if ($status == true) {
            $res->data = $payload;
        } else {
            $res->errors = $payload;
        }

        return $res;
    }
}

if (! function_exists('auth_name')) {
    function auth_name(){
        $user = request()->user();

        return $user != null ? $user->name : '';
    }
}

if (! function_exists('auth_id')) {
    function auth_id(){
        $user = request()->user();

        return $user != null ? $user->id : null;
    }
}

if (! function_exists('customer_name')) {
    function customer_name($customer){
        if ($customer) {
            $first = $customer->other_names;
            $surname = $customer->surname;
            return $surname . ', ' . $first;
        }
        return '';
    }
}

if (! function_exists('safe_indexing')) {
    function safe_indexing(array $ar, $index, $default = null){
        return array_key_exists($index, $ar) ? $ar[$index] : $default;
    }
}

if (! function_exists('glue_errors')) {

    function glue_errors(stdClass $response){
        $errors = flatten_validation_error($response->errors);
        $errors = implode("|", $errors);
        $response->errors = ['message' =>$errors, 'error' => $response->errors];
        return $response;
    }
}

if (! function_exists('flatten_validation_error')) {

    /**
     * @param $errors
     * @return array
     */
    function flatten_validation_error($errors){
        $errors_array = [];
        foreach (collect($errors) as $error) {
            foreach ($error as $err){
                array_push($errors_array, $err);
            }
        }
        return $errors_array;
    }
}

if (! function_exists('helper_response')) {

    /**
     * @param stdClass $res
     * @return JsonResponse
     */
    function helper_response(stdClass $res){

        if (!$res->status) {
            return response()->json($res->errors ?? '', $res->statusCode ?? Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json($res->data, Response::HTTP_OK);
    }
}

if (! function_exists('pretty_date')) {

    function pretty_date($date){

        return Carbon::parse($date)->format('j F Y');
    }
}

if (! function_exists('pretty_date_with_time')) {

    function pretty_date_with_time($date){

        return Carbon::parse($date)->format('j F Y g:i:s a');
    }
}

if (! function_exists('auth_company_id')) {
    function auth_company_id(){
        $user = request()->user();
        $request_user_company_id = request()->get('company_id');

        return $user != null ? $user->company_id != null ? $user->company_id : $request_user_company_id:$request_user_company_id;
    }
}

if (! function_exists('auth_branch_id')) {
    function auth_branch_id(){
        $user = request()->user();
        $request_user_branch_id = request()->get('branch_id');

        return $user != null ? $user->branch_id != null ? $user->branch_id : $request_user_branch_id:$request_user_branch_id;
    }
}

if (! function_exists('pad_zeros')) {
    function pad_zeros($number){
        if ($number < 10) {
            return '000' . $number;
        }

        if ($number < 100) {
            return '00' . $number;
        }

        if ($number < 1000) {
            return '0' . $number;
        }

        return $number;

    }
}

if (! function_exists('get_sexes')) {
    function get_sexes(){
        return ['Male'=>'Male', 'Female'=>'Female'];
    }
}

if (! function_exists('get_titles')) {
    function get_titles(){
        return ['Mr' => 'Mr', 'Mrs' => 'Mrs', 'Miss' => 'Miss', 'Dr' => 'Dr', 'Prof' => 'Prof',
            'Rev' => 'Rev', 'Rev Dr' => 'Rev Dr'];
    }
}

if (! function_exists('get_statuses')) {
    function get_statuses(){
        return [
            ['label' => 'Active', 'value' => 1],
            ['label' => 'Dormant', 'value' => 2],
            ['label' => 'Closed', 'value' => 3]
        ];
    }
}

if (! function_exists('statuses')) {
    function statuses(){
        return [
            '1' => 'Active', '2' => 'Dormant', '3' => 'Closed'
        ];
    }
}

if (! function_exists('user_statuses')) {
    function user_statuses(){
        return [
            '1' => 'Active', '2' => 'Suspended', '0' => 'Blocked'
        ];
    }
}

if (! function_exists('get_transaction_types')) {
    function get_transaction_types(){
        return [
            ['label' => 'Deposit', 'value' => TransactionType::$DEPOSIT],
            ['label' => 'Withdrawal', 'value' => TransactionType::$WITHDRAWAL]
        ];
    }
}

if (! function_exists('transaction_types')) {
    function transaction_types(): array
    {
        return [
            TransactionType::$DEPOSIT => 'Deposit', TransactionType::$WITHDRAWAL => 'Withdrawal'
        ];
    }
}

if (! function_exists('pretty_amount')) {
    function pretty_amount($amount){
        return number_format($amount, 2, '.', ',');
    }
}

if (! function_exists('date_extractor')) {
    function date_extractor($dates){
        $date = explode(' - ', request()->dates);

        return [Carbon::parse($date[0])->setTime(00,00),Carbon::parse($date[1])->setTime(23,59)];
    }
}
if (! function_exists('user_not_found')) {
    function user_not_found(){
        return prepareResponse(false, ['message' => "User Not Found", "error" => "Not Found"],
            Response::HTTP_NOT_FOUND);
    }
}

if (! function_exists('get_dates')) {
    function get_dates($month = true){
        $from = request()->get('from');
        $to = request()->get('to');

        $from = $from  ? (Carbon::parse($from)): (($month == true) ? now()->startOfMonth(): now());
        $to = $to ? (Carbon::parse($to)) : (($month == true) ? Carbon::parse($from)->endOfMonth() : now());

        return [$from->setHours(0)->setMinute(0), $to->setHours(23)->setMinute(59)];
    }
}

if (! function_exists('days_of_week')) {
    function days_of_week(){
        return [
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
        ];
    }
}

if (! function_exists('month_names')) {
    function month_names(){
        return [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
    }
}

if (! function_exists('percentage_calc')) {
    function percentage_calc($new = 0, $old = 0): float|int
    {
       if ($old == 0 ) return 0;

       $calc = (($new - $old)/ $old) * 100;
       return ceil($calc);

    }
}

if (! function_exists('non_super_levels')) {
    function non_super_levels(): array
    {
       return ['director' => 'director', 'manager' => 'manager', 'agent' => 'agent'];

    }
}

if (! function_exists('super_levels')) {
    function super_levels(): array
    {
        return [...non_super_levels(), 'super' => 'super'];

    }
}

if (! function_exists('report_date_description')) {
    function report_date_description($start, $end): string
    {
        $now = Carbon::now();
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // This week
        if ($start->isSameWeek($now) && $end->isSameWeek($now)) {
            return 'This week - ' . pretty_date($end);
        }

        // This month
        if ($start->isSameMonth($now) && $end->isSameMonth($now)) {
            return $start->format('F Y');
        }

        // This year
        if ($start->isSameYear($now) && $end->isSameYear($now)) {
            return $start->format('Y');
        }

        // Specific month and year
        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('F Y');
        }

        // Specific year
        if ($start->format('Y') === $end->format('Y')) {
            return $start->format('Y');
        }

        // Default
        return pretty_date($start) . ' - ' . pretty_date($end);
    }

}

if (! function_exists('last_transaction_date')) {
    function last_transaction_date($customer_id)
    {
        $transaction = Transaction::where('customer_id', $customer_id)->orderByDesc('date')->first(['date', 'type']);
        return $transaction ? pretty_date($transaction->date) : '';

    }
}

if (! function_exists('format_special_message')) {
    function format_special_message($special_message, $amount): string
    {
        $amount = pretty_amount($amount);
        return Str::replace('$AMOUNT',"GHS $amount", $special_message);

    }
}

if (! function_exists('get_repayment')) {
    function get_repayment(Loan $loan)
    {
        if (!$loan || !$loan->balance) {
            return 0.00;
        }

        return $loan->amount - $loan->balance->amount;
    }
}






