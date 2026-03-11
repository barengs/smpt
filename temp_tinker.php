<?php
$data = \App\Models\StudentClass::with('students')->where('class_group_id', 25)->get();
echo json_encode($data);
