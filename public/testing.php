<pre>
<?php
    $arr1 = array(
        "value" => "test",
        "other" => array(
            "here" => "too",
            "this" => array(
                "is" => array(
                    "some" => "more",
                    "a" => "test"
                )
            )
        )
    );
    
    $arr2 = array(
        "value" => "test1",
        "other" => array(
            "here" => "too",
            "this" => array(
                "is" => array(
                    "some" => "more2",
                    "actually" => "working"
                )
            )
        )
    );

    print_r(json_encode(array_replace_recursive($arr1, $arr2)));