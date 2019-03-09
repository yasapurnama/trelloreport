<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if ( ! function_exists('parseEstimate'))
{
    function parseEstimate($str){
        $task_id        = "Undefined";
        $hours          = 0;
        $minutes        = 0;
        $estimate       = 0;
        $data_arr       = [];
        preg_match('/(.*)est[\.|i]?+[m]?+[a]?+[s]?+[i]?+[\.]?+(.*)j[a]?+[m]?/i', $str, $output_array);
        if(count($output_array) == 3){
            $task_id    = $output_array[1];
            $hours      = str_replace(' ', '', str_replace(',', '.', $output_array[2]));
        }
        preg_match('/(\d+)+[\s]?+m[e]?+[n]?+[i]?+[t]?/i', $str, $output_array2);
        if(count($output_array2) == 2 && $output_array2[1] < 60){
            $minutes    = round(($output_array2[1]/60), 2);
            if($task_id == "Undefined"){
                preg_match('/(.*)est[\.|i]?+[m]?+[a]?+[s]?+[i]?/i', $str, $output_array3);
                if(count($output_array3) == 2){
                    $task_id = $output_array3[1];
                }
            }
        }
        $estimate = $hours + $minutes;
        $data_arr = [
                "task_id" => $task_id,
                "estimate" => $estimate
            ];
        return $data_arr;
    } 
}


/**
 * ------------------------------------------------------------------------
 *
 * dDebug Helper
 *
 * Outputs the given variable(s) with formatting and location
 *
 * @access    public
 * @param    mixed    - variables to be output
 */
if ( ! function_exists('dDebug'))
{
    function dDebug()
    {
        list($callee) = debug_backtrace();

        $args = func_get_args();

        $total_args = func_num_args();

        echo '<div><fieldset style="background: #fefefe !important; border:1px red solid; padding:15px">';
        echo '<legend style="background:blue; color:white; padding:5px;">'.$callee['file'].' @line: '.$callee['line'].'</legend><pre><code>';

        $i = 0;

        foreach ($args as $arg)
        {
            echo '<strong>Debug #' . ++$i . ' of ' . $total_args . '</strong>: ' . '<br>';

            var_dump($arg);
        }

        echo "</code></pre></fieldset><div><br>";
    }
} 
