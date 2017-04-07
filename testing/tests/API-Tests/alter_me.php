<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 4/7/2017
 * Time: 11:38 AM
 * Type: JS
 * Test Name: Change Account values
 */

?>
<script>
    function test(test){
        $.ajax({
            type: 'post',
            url: '/api/me/',
            dataType: 'JSON',
            data: {
                FirstName:"Bob",
                LastName:"Ross",
                Email:"bobross.com",
                Name:"Fluffy Clouds",
                Token:"new"
            },
            success: function (data) {
                test.log(JSON.stringify(data));
                test.end(true);
            },
            error: function (error) {
                console.log(error);
                test.end(false);
            }
        });
    }
</script>
