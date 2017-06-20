<?php
/**
 * ClassName: Invoice
 * Table Name: Invoice,Invoice has payments 
 **/

class Invoice extends ActiveRecord\Model {
    static $belongs_to = array(
    array('company'),
    array('project')
    );
    static $has_many = array(
    array('invoice_has_items'),
    array('invoice_has_payments'),
    array('items', 'through' => 'invoice_has_items')
 	);

    /**
    ** Get sum of income for given year
    ** return object
    **/
    public static function totalIncomeForYear($year){
        $income = Invoice::find_by_sql("SELECT 
            SUM(`sum`) as summary
        FROM
            ((SELECT 
                SUM(`sum`) AS `sum`
            FROM
                invoices
            WHERE
                `status` = 'Paid' 
            AND 
                `paid` = '0'
            AND 
                paid_date BETWEEN '$year-01-01' AND '$year-12-31'
            ) 
            UNION ALL (SELECT 
                SUM(T3.`amount`) AS `sum`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                (T4.`status` = 'PartiallyPaid' OR (T4.`status` = 'Paid' AND T4.`paid` != '0'))
                AND 
                T3.`date` 
                    BETWEEN '$year-01-01' AND '$year-12-31' 

            ) ) t1
                    ");

        return $income[0]->summary;
    }

    
    public static function totalIncomeForYearByUserId($cid,$user_id,$year){
        $income = Invoice::find_by_sql("SELECT 
            SUM(`sum`) as summary
        FROM
            ((SELECT 
                SUM(`sum`) AS `sum`
            FROM
                invoices
            WHERE
                `status` = 'Paid' 
            AND 
                `paid` = '0'
            AND 
                paid_date BETWEEN '$year-01-01' AND '$year-12-31'
            AND
               `user_id`= '".$user_id."'
            AND
               `company_id`= '".$cid."'
            ) 
            UNION ALL (SELECT 
                SUM(T3.`amount`) AS `sum`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                (T4.`status` = 'PartiallyPaid' OR (T4.`status` = 'Paid' AND T4.`paid` != '0'))
                AND 
                T3.`date` 
                    BETWEEN '$year-01-01' AND '$year-12-31' 
                AND
                T4.user_id= '".$user_id."'
                AND
                company_id= '".$cid."'
            ) ) t1
                    ");

        return $income[0]->summary;
    }
    /**
    ** Get sum of payments grouped by Month for statistics
    ** return object
    **/

    /*
    public static function getStatisticForYear($year){
        $incomeStats = Invoice::find_by_sql("SELECT 
            paid_date as paid_date, 
            SUM(`sum`) AS summary
        FROM
            ((SELECT 
                paid_date AS `paid_date`, 
                `status`, 
                SUM(`sum`) AS `sum`
            FROM
                invoices
            WHERE
                `status` = 'Paid' AND `paid` = '0'
            AND 
                paid_date BETWEEN '$year-01-01' AND '$year-12-31'
            GROUP BY 
            SUBSTR(`paid_date`, 1, 7) 
            ) 
            UNION ALL (SELECT 
                T3.`date` AS `paid_date`, 
                T4.`status`, 
                SUM(T3.`amount`) AS `sum`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                (T4.`status` = 'PartiallyPaid' OR (T4.`status` = 'Paid' AND T4.`paid` != '0'))
                AND 
                T3.`date` BETWEEN '$year-01-01' AND '$year-12-31' 
            GROUP BY 
                SUBSTR(T3.`date`, 1, 7) 
            ) )t1 
            GROUP BY 
                SUBSTR(`paid_date`, 1, 7);
            ");

    return $incomeStats;
    }
*/
    public static function getStatisticForYear($year){
        $incomeStats = Invoice::find_by_sql("SELECT 
            SUBSTR(`paid_date`, 1, 7) as `paid_date`, 
            SUM(`summary`) AS `summary`
        FROM
            ((SELECT 
                SUBSTR(`paid_date`, 1, 7) AS `paid_date`, 
                SUM(`sum`) AS `summary`
            FROM
                invoices
            WHERE
                `status` = 'Paid' 
            AND 
                `paid` = '0'
            AND 
                `paid_date` BETWEEN '$year-01' AND '$year-12'
            GROUP BY 
            `paid_date`
            ) 
            UNION ALL (SELECT 
                SUBSTR(T3.`date`, 1, 7) AS `paid_date`, 
                SUM(T3.`amount`) AS `summary`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                (T4.`status` = 'PartiallyPaid' OR (T4.`status` = 'Paid' AND T4.`paid` != '0'))
                AND 
                `paid_date` BETWEEN '$year-01' AND '$year-12' 
            GROUP BY 
                `paid_date`, T3.`date`
            ) )t1 
            GROUP BY 
                t1.`paid_date`;
            ");

    return $incomeStats;
    }
    
    // get year statistics by user_role
    public static function getStatisticForYearByUserId($user_id,$year){
        $incomeStats = Invoice::find_by_sql("SELECT 
            SUBSTR(`paid_date`, 1, 7) as `paid_date`, 
            SUM(`summary`) AS `summary`
        FROM
            ((SELECT 
                SUBSTR(`paid_date`, 1, 7) AS `paid_date`, 
                SUM(`sum`) AS `summary`
            FROM
                invoices
            WHERE
                `status` = 'Paid' 
            AND 
                `paid` = '0'
            AND
                `user_id`= '".$user_id."'
            AND 
                `paid_date` BETWEEN '$year-01' AND '$year-12'
            GROUP BY 
            `paid_date`
            ) 
            UNION ALL (SELECT 
                SUBSTR(T3.`date`, 1, 7) AS `paid_date`, 
                SUM(T3.`amount`) AS `summary`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                (T4.`status` = 'PartiallyPaid' OR (T4.`status` = 'Paid' AND T4.`paid` != '0'))
                AND 
                 T4.user_id= '".$user_id."'
                AND
                `paid_date` BETWEEN '$year-01' AND '$year-12' 
            GROUP BY 
                `paid_date`, T3.`date`
            ) )t1 
            GROUP BY 
                t1.`paid_date`;
            ");

        return $incomeStats;
    }
    
    /**
    ** Get sum of payments grouped by Month for statistics
    ** return object
    **/
    public static function getExpensesStatisticForYear($year){
       $expensesByMonth = Expense::find_by_sql("SELECT 
                SUBSTR(`date`, 1, 7) as `date_month`,
                SUM(`value`) AS summary
            FROM 
                `expenses` 
            WHERE 
                `date` BETWEEN '$year-01' AND '$year-12' 
            Group BY 
                `date_month`
            ");

        return $expensesByMonth;
    }
    
    // get year expenses by user_role
    public static function getExpensesStatisticForYearByUserId($user_id,$year){
       $expensesByMonth = Expense::find_by_sql("SELECT 
                SUBSTR(`date`, 1, 7) as `date_month`,
                SUM(`value`) AS summary
            FROM 
                `expenses` 
            WHERE 
                `date` BETWEEN '$year-01' AND '$year-12' 
            AND
                `user_id`='".$user_id."'
            Group BY 
                `date_month`
            ");

        return $expensesByMonth;
    }
     /**
    ** Get sum of payments grouped by Month for statistics
    ** return object
    **/
    public static function getExpensesStatisticFor($start, $end){
       $expensesByMonth = Expense::find_by_sql("SELECT 
                SUBSTR(`date`, 1, 7) as `date_month`,
                SUM(`value`) AS summary
            FROM 
                `expenses` 
            WHERE 
                `date` BETWEEN '$start' AND '$end' 
            Group BY 
                `date_month`;
            ");

        return $expensesByMonth;
    }

    /**
    ** Get sum of payments made in the given Month
    ** return object
    **/
    public static function paymentsForMonth($yearMonth){
        $Paid = Invoice::find_by_sql("SELECT 
            SUM(`sum`) as summary
        FROM
            ((SELECT 
                SUM(`sum`) AS `sum`
            FROM
                invoices
            WHERE
                `status` = 'Paid' 
            AND 
                `paid` = '0' 
            AND 
                paid_date BETWEEN '$yearMonth-01' AND '$yearMonth-31'
            Group By 
                `sum`
            ) 
            UNION ALL (SELECT 
                SUM(T3.`amount`) AS `sum`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                (T4.`status` = 'PartiallyPaid' OR (T4.`status` = 'Paid' AND T4.`paid` != '0'))
                AND 
                T3.`date` BETWEEN '$yearMonth-01' AND '$yearMonth-31'  
            GROUP BY 
                `sum`
            ) ) t1
            ");

        return $Paid[0]->summary;
    }
    
    //get payments by user role
    public static function paymentsForMonthByUserId($cid,$user_id,$yearMonth){
        
        $Paid = Invoice::find_by_sql("SELECT 
            SUM(`sum`) as summary
        FROM
            ((SELECT 
                SUM(`sum`) AS `sum`
            FROM
                invoices
            WHERE
                `status` = 'Paid' 
            AND 
                `paid` = '0' 
            AND
                `user_id` = '".$user_id."'
            AND
                `company_id` = '".$cid."'
            AND 
                paid_date BETWEEN '$yearMonth-01' AND '$yearMonth-31'
            Group By 
                `sum`
            ) 
            UNION ALL (SELECT 
                SUM(T3.`amount`) AS `sum`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                (T4.`status` = 'PartiallyPaid' OR (T4.`status` = 'Paid' AND T4.`paid` != '0'))
                AND 
                T3.`date` BETWEEN '$yearMonth-01' AND '$yearMonth-31' 
                AND
                T4.user_id  ='".$user_id."' AND T4.company_id  ='".$cid."'
            GROUP BY 
                `sum`
            ) ) t1
            ");

        return $Paid[0]->summary;
    }
    /**
    ** Get sum of outstanding payments 
    ** return object
    **/
    public static function outstandingPayments($yearMonth = FALSE){
        $where = "";
        if($yearMonth){
            $where = " AND due_date BETWEEN '$yearMonth-01' AND '$yearMonth-31'";
        }
        $open = Invoice::find_by_sql("SELECT 
                sum(invoices.`sum`) as `summary` 
            FROM 
                invoices 
            WHERE 
                (invoices.`status` = 'Sent' 
            OR 
                invoices.`status` = 'Open') 
            AND 
                invoices.`estimate` != 1
            $where 
            ");

        $partially = Invoice::find_by_sql("SELECT 
                sum(invoices.`outstanding`) as summary 
            FROM 
                invoices 
            WHERE 
                invoices.`status` = 'PartiallyPaid'
            $where;
            ");
        $open[0]->summary = $open[0]->summary+$partially[0]->summary;
        return $open[0]->summary;
    }

    
    //paymenments for user role
    public static function outstandingPaymentsByUserId($cid=FALSE,$user_id=FALSE,$yearMonth = FALSE){
        $where = "";
        if($yearMonth){
            $where = " AND due_date BETWEEN '$yearMonth-01' AND '$yearMonth-31'";
        }
        if($cid)
        {
            $where1=" AND invoices.`company_id` = '".$cid."'";
        }
        if($user_id)
        {
            $where2=" AND invoices.`user_id` = '".$user_id."'";
        }
        $open = Invoice::find_by_sql("SELECT 
                sum(invoices.`sum`) as `summary` 
            FROM 
                invoices 
            WHERE 
                (invoices.`status` = 'Sent' 
            OR 
                invoices.`status` = 'Open')
            $where2
            $where1
            AND 
                invoices.`estimate` != 1
            $where 
            ");

        $partially = Invoice::find_by_sql("SELECT 
                sum(invoices.`outstanding`) as summary 
            FROM 
                invoices 
            WHERE 
                invoices.`status` = 'PartiallyPaid'
                $where2
                $where1
            $where;
            ");
        $open[0]->summary = $open[0]->summary+$partially[0]->summary;
        //echo $open[0]->summary;exit;
        return $open[0]->summary;
    }
    /**
    ** Get sum of outstanding payments 
    ** return object
    **/
    public static function totalExpensesForYear($year){
        $expenses = Expense::find_by_sql("SELECT 
                SUM(`value`) AS summary
            FROM 
                `expenses` 
            WHERE 
                `date` BETWEEN '$year-01-01' AND '$year-12-31' 
            ");

        return $expenses[0]->summary;
    }
    
    public static function totalExpensesForYearByUserId($user_id,$year){
        $expenses = Expense::find_by_sql("SELECT 
                SUM(`value`) AS summary
            FROM 
                `expenses` 
            WHERE 
                `date` BETWEEN '$year-01-01' AND '$year-12-31' 
              AND
              `user_id`='".$user_id."'
            ");

        return $expenses[0]->summary;
    }
    public static function overdueByDate($comp_array, $date){
        $filter = "";
        if($comp_array != FALSE)
        {
          $filter = " company_id in (".$comp_array.") AND ";
        }
        $invoices = Invoice::find_by_sql("SELECT 
                `reference`, 
                `id`, 
                `due_date` 
            FROM 
                `invoices`
            WHERE 
                    $filter

                    `status` != 'Paid'
                AND 
                    `status` != 'Canceled' 
                AND 
                    `due_date` < '$date' AND `estimate` != 1 ORDER BY `due_date` 
                
            ");
        
        return $invoices;
    }

        /**
    ** Get sum of payments grouped by Month for statistics
    ** return object
    **/
    public static function getStatisticFor($start, $end,$cid){
        
        $incomeStats = Invoice::find_by_sql("SELECT 
            paid_date as paid_date, 
            SUM(`sum`) AS summary
        FROM
            ((SELECT 
                paid_date AS `paid_date`, 
                SUM(`sum`) AS `sum`
            FROM
                invoices
            WHERE
                `status` = 'Paid'
            AND 
                paid_date BETWEEN '$start' AND '$end'
            AND
                company_id = '$cid'
            GROUP BY 
            SUBSTR(`paid_date`, 1, 7), paid_date 
            ) 
            UNION ALL (SELECT 
                T3.`date` AS `paid_date`, 
                SUM(T3.`amount`) AS `sum`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                T4.`status` = 'PartiallyPaid' 
                AND 
                T3.`date` BETWEEN '$start' AND '$end' 
                AND
                T4.company_id = '$cid'
            GROUP BY 
                SUBSTR(T3.`date`, 1, 7) , T3.`date`
            ) )t1 
            GROUP BY 
                SUBSTR(`paid_date`, 1, 7), paid_date;
            ");

    return $incomeStats;
    }

         /**
    ** Get sum of payments grouped by Month for statistics
    ** return object
    **/
    public static function getStatisticForClients($start, $end,$cid){
        $incomeStats = Invoice::find_by_sql("SELECT 
            company_id as `company_id`, 
            SUM(`sum`) AS summary
        FROM
            ((SELECT 
                company_id as `company_id`, 
                SUM(`sum`) AS `sum`
            FROM
                invoices
            WHERE
                `status` = 'Paid'
            AND 
                paid_date BETWEEN '$start' AND '$end'
            AND
                company_id= '$cid'
            GROUP BY 
            company_id
            ) 
            UNION ALL (SELECT 
                T4.company_id as `company_id`, 
                SUM(T3.`amount`) AS `sum`
            FROM
                invoice_has_payments AS T3
            LEFT JOIN
                invoices AS T4
            ON 
                T3.invoice_id = T4.id  
            WHERE 
                T4.`status` = 'PartiallyPaid' 
                AND 
                T3.`date` BETWEEN '$start' AND '$end' 
                AND
                T4.company_id= '$cid'
            GROUP BY 
                T4.company_id
            ) )t1 
            GROUP BY 
                company_id
            ");

    return $incomeStats;
    }
}

class InvoiceHasPayment extends ActiveRecord\Model {
    static $belongs_to = array(
    array('invoice'),
    array('user')
    );
}

class InvoiceHasItem extends ActiveRecord\Model {
   	static $belongs_to = array(
    array('invoice'),
    array('item')
    );
}

class Item extends ActiveRecord\Model {
   	static $has_many = array(
    array('invoice_has_items')
    );
} 