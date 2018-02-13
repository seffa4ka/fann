<?php

$arg = 'test';

if (isset($argv[1])) {
    $arg = $argv[1];
}

switch ($arg) {
    case 'test':
        $items = [
            [-1, -1, -1],
            [-1, 1, 1],
            [1, -1, 1],
            [1, 1, -1],
        ];

        foreach ($items as $item) {
            $train_file = (dirname(__FILE__) . "/network.txt");
            if (!is_file($train_file))
                die("File network.txt not exist!" . PHP_EOL);

            $ann = fann_create_from_file($train_file);
            if (!$ann)
                die("Network could not be created!" . PHP_EOL);

            $input = array($item[0], $item[1]);
            $calc_out = fann_run($ann, $input);
            printf("xor test (%f,%f)\t-> %f\t|\t%f\n", $input[0], $input[1], $calc_out[0], $item[2]);
            fann_destroy($ann);
        }

        echo 'Complete!' . PHP_EOL;
        break;
    case 'start':
        if (!isset($argv[2]) || !isset($argv[3])) {
            die('Need arguments!' . PHP_EOL);
        }

        if (!($argv[2] === '1' || $argv[2] === '-1') || !($argv[3] === '1' || $argv[3] === '-1')) {
            die('Arguments can be 1 or -1!' . PHP_EOL);
        }

        $train_file = (dirname(__FILE__) . "/network.txt");
        if (!is_file($train_file))
            die("File network.txt not exist!" . PHP_EOL);

        $ann = fann_create_from_file($train_file);
        if (!$ann)
            die("Network could not be created!" . PHP_EOL);

        $input = array((int)$argv[2], (int)$argv[3]);
        $calc_out = fann_run($ann, $input);
        printf("xor test (%f,%f) -> %f\n", $input[0], $input[1], $calc_out[0]);
        fann_destroy($ann);
        echo 'Complete!' . PHP_EOL;
        break;
    case 'learn':
        $num_input = 2;
        $num_output = 1;
        $num_layers = 3;
        $num_neurons_hidden = 3;
        $desired_error = 0.001;
        $max_epochs = 500000;
        $epochs_between_reports = 1000;

        $ann = fann_create_standard($num_layers, $num_input, $num_neurons_hidden, $num_output);

        if ($ann) {
            fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);

            $filename = dirname(__FILE__) . "/data.txt";

            if (file_exists($filename)) {
                if (fann_train_on_file($ann, $filename, $max_epochs, $epochs_between_reports, $desired_error))
                    fann_save($ann, dirname(__FILE__) . "/network.txt");

                fann_destroy($ann);
            } else {
                echo 'File data.txt not found. Loading data from an array.';

                function getData()
                {
                    return [
                        [[-1, -1], [-1]],
                        [[-1, 1], [1]],
                        [[1, -1], [1]],
                        [[1, 1], [-1]],
                    ];
                }

                function create_train_callback(&$num_data, $num_input, $num_output)
                {
                    $num_data--;
                    echo $num_data . ': input ' . $num_input . ' | output ' . $num_output . PHP_EOL;

                    $arr = getData();
                    if (isset($arr[$num_data])) {
                        return $arr[$num_data];
                    }

                    echo 'Error array data.' . PHP_EOL;
                    return null;
                }

                $num_data = 4;
                $num_input = 2;
                $num_output = 1;
                $data = fann_create_train_from_callback($num_data, $num_input, $num_output, "create_train_callback");

                if ($data) {
                    if (fann_train_on_data($ann, $data, $max_epochs, $epochs_between_reports, $desired_error))
                        fann_save($ann, dirname(__FILE__) . "/network.txt");

                    fann_destroy($ann);
                } else {
                    echo 'Error create train.' . PHP_EOL;
                }
            }
        }
        echo 'Complete!' . PHP_EOL;
        break;
    case 'help':
        echo 'Commands:' . PHP_EOL;
        echo 'test  - test your data.' . PHP_EOL;
        echo 'learn - learn network.' . PHP_EOL;
        echo 'help  - help.' . PHP_EOL;
        break;
    default:
        echo 'Bad argument!' . PHP_EOL;
        break;
}
