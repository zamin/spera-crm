<?php

/**
 * ClassName: API Projects 
 * This class is used for All Projects API
 * */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Projects extends MY_Api_Controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->settings = Setting::first();
        $this->headers = apache_request_headers();
        //echo "<pre>";print_r($this->headers);exit;
//        $user_access_token = $this->headers['User-Access-Token'] ? $this->headers['User-Access-Token'] : '';
//        if (empty($user_access_token)) {
//            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'Access token not found'));
//            $this->response($newdata);
//        }
//
//        $this->headers['User-Login-Token'] = $this->headers['User-Login-Token'] ? $this->headers['User-Login-Token'] : '';
//        if (empty($this->headers['User-Login-Token'])) {
//            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'Login token not found'));
//            $this->response($newdata);
//        }
//        $this->user_id = $this->checklogin($user_access_token, $this->headers['User-Login-Token']);
        $user_access_token = $this->headers['user_access_token'] ? $this->headers['user_access_token'] : '';
        if (empty($user_access_token)) {
            $newdata = array('result' => 'error', 'response' => 'Access token not found');
            $this->response($newdata);
        }

        $this->headers['user_login_token'] = $this->headers['user_login_token'] ? $this->headers['user_login_token'] : '';
        if (empty($this->headers['user_login_token'])) {
            $newdata = array('result' => 'error', 'response' => 'Login token not found');
            $this->response($newdata);
        }
        $this->user_id = $this->checklogin($user_access_token, $this->headers['user_login_token']);
    }

    /* All Projects */

    function allprojects() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => "no Data Found"));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => "no Data Found"));
            $this->response($newdata);
        }
        $status = trim(htmlspecialchars($_REQUEST['status'])) ? trim(htmlspecialchars($_REQUEST['status'])) : '';
        if (empty($status)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Projects Status is blank'));
            $this->response($newdata);
        }
        $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                        LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
        if (empty($get_data)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
            $this->response($newdata);
        }
        $role_id = $get_data['role_id'];
        $company_id = $get_data['company_id'];
        $get_compnay = Company::find_by_id($company_id);
        $options_closed = 'p.progress = 100';
        $options_open = 'p.progress < 100';
        if ($status == 'all') {
            if ($role_id == 2) {
                $project = Project::find_by_sql('SELECT DISTINCT (p.id),p.* FROM projects p LEFT JOIN user_roles u ON p.company_id = u.company_id WHERE u.company_id = "' . $company_id . '" AND u.role_id = "' . $role_id . '" and u.user_id="' . $this->user_id . '" order by p.id desc');
                if (empty($project)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any Projects"));
                    $this->response($newdata);
                }

                $project_all = array();
                $i = 0;
                foreach ($project as $key => $value) {
                    $project_all[$i]['id'] = $value->id;
                    $project_all[$i]['name'] = $value->name;
                    $project_all[$i]['reference'] = $value->reference;
                    if (!empty($value->description)) {
                        $project_all[$i]['description'] = strip_tags($value->description);
                    } else {
                        $project_all[$i]['description'] = "";
                    }
                    //$project_all[$i]['description'] = $value->description;
                    $project_all[$i]['start'] = $value->start;
                    $project_all[$i]['end'] = $value->end;
                    $project_all[$i]['company_name'] = $get_compnay->name;
                    $project_all[$i]['progress'] = $value->progress;
                    $project_all[$i]['phases'] = $value->phases;
                    $project_all[$i]['reference'] = $value->reference;
                    if ($value->tracking == '') {
                        $project_all[$i]['tracking'] = 0;
                    } else {
                        $project_all[$i]['tracking'] = $value->tracking;
                    }
                    $project_all[$i]['datetime'] = $value->datetime;
                    $project_all[$i]['company_id'] = $value->company_id;

                    $assign_client_details = $this->db->query('SELECT DISTINCT (p.assign_user_id), u.* FROM project_assign_clients p
                                                            LEFT JOIN user_roles r ON p.company_id = r.company_id
                                                            LEFT JOIN users u ON p.assign_user_id = u.id
                                                            WHERE p.project_id = "' . $value->id . '"
                                                            AND r.company_id = "' . $company_id . '" AND r.role_id="' . $role_id . '" and u.status="active"')->result_array();
                    if (empty($assign_client_details)) {
                        $project_all[$i]['clients'] = "";
                    } else {
                        $j = 0;
                        foreach ($assign_client_details as $key1 => $value1) {
                            $project_all[$i]['clients'][$j]['user_id'] = $value1['assign_user_id'];
                            $project_all[$i]['clients'][$j]['firstname'] = $value1['firstname'];
                            $project_all[$i]['clients'][$j]['lastname'] = $value1['lastname'];
                            $project_all[$i]['clients'][$j]['email'] = $value1['email'];
                            $project_all[$i]['clients'][$j]['userpic'] = $value1['userpic'];
                            $j++;
                        }
                    }
                    $i++;
                }
                if (empty($project_all)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $project_all, 'code' => 200);
                $this->response($newdata);
            }

            $project = Project::find_by_sql("SELECT p.* from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where r.company_id='" . $company_id . "' and r.role_id='" . $role_id . "' and r.user_id='" . $this->user_id . "' order by p.id desc");
            if (empty($project)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any Projects"));
                $this->response($newdata);
            }

            $project_all = array();
            $i = 0;
            foreach ($project as $key => $value) {
                $project_all[$i]['id'] = $value->id;
                $project_all[$i]['name'] = $value->name;
                $project_all[$i]['reference'] = $value->reference;
                if (!empty($value->description)) {
                    $project_all[$i]['description'] = strip_tags($value->description);
                } else {
                    $project_all[$i]['description'] = "";
                }
                $project_all[$i]['start'] = $value->start;
                $project_all[$i]['end'] = $value->end;
                $project_all[$i]['company_name'] = $get_compnay->name;
                $project_all[$i]['progress'] = $value->progress;
                $project_all[$i]['phases'] = $value->phases;
                $project_all[$i]['reference'] = $value->reference;
                if ($value->tracking == '') {
                    $project_all[$i]['tracking'] = 0;
                } else {
                    $project_all[$i]['tracking'] = $value->tracking;
                }
                $project_all[$i]['datetime'] = $value->datetime;
                $project_all[$i]['company_id'] = $value->company_id;
                $i++;
            }
            if (empty($project_all)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $project_all, 'code' => 200);
            $this->response($newdata);
        }
        if ($status == 'open') {
            if ($role_id == 2) {
                $project = Project::find_by_sql('SELECT DISTINCT (p.id),p.* from projects p left join user_roles r on p.company_id=r.company_id where p.company_id="' . $company_id . '" and r.role_id="' . $role_id . '" and r.user_id="' . $this->user_id . '" and ' . $options_open . '');
                if (empty($project)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any Projects"));
                    $this->response($newdata);
                }

                $project_all = array();
                $i = 0;
                foreach ($project as $key => $value) {
                    $project_all[$i]['id'] = $value->id;
                    $project_all[$i]['name'] = $value->name;
                    $project_all[$i]['reference'] = $value->reference;
                    if (!empty($value->description)) {
                        $project_all[$i]['description'] = strip_tags($value->description);
                    } else {
                        $project_all[$i]['description'] = "";
                    }

                    $project_all[$i]['start'] = $value->start;
                    $project_all[$i]['end'] = $value->end;
                    $project_all[$i]['company_name'] = $get_compnay->name;
                    $project_all[$i]['progress'] = $value->progress;
                    $project_all[$i]['phases'] = $value->phases;
                    $project_all[$i]['reference'] = $value->reference;
                    if ($value->tracking == '') {
                        $project_all[$i]['tracking'] = 0;
                    } else {
                        $project_all[$i]['tracking'] = $value->tracking;
                    }
                    $project_all[$i]['datetime'] = $value->datetime;
                    $project_all[$i]['company_id'] = $value->company_id;

                    $assign_client_details = $this->db->query('SELECT DISTINCT (p.assign_user_id), u.* FROM project_assign_clients p
                                                            LEFT JOIN user_roles r ON p.company_id = r.company_id
                                                            LEFT JOIN users u ON p.assign_user_id = u.id
                                                            WHERE p.project_id = "' . $value->id . '"
                                                            AND r.company_id = "' . $company_id . '" AND r.role_id="' . $role_id . '" and u.status="active"')->result_array();
                    if (empty($assign_client_details)) {
                        $project_all[$i]['clients'] = "";
                    } else {
                        $j = 0;
                        foreach ($assign_client_details as $key1 => $value1) {
                            $project_all[$i]['clients'][$j]['user_id'] = $value1['assign_user_id'];
                            $project_all[$i]['clients'][$j]['firstname'] = $value1['firstname'];
                            $project_all[$i]['clients'][$j]['lastname'] = $value1['lastname'];
                            $project_all[$i]['clients'][$j]['email'] = $value1['email'];
                            $project_all[$i]['clients'][$j]['userpic'] = $value1['userpic'];
                            $j++;
                        }
                    }
                    $i++;
                }
                if (empty($project_all)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $project_all, 'code' => 200);
                $this->response($newdata);
            }

            $project = Project::find_by_sql("SELECT p.* from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where " . $options_open . " and r.company_id='" . $company_id . "' and r.role_id='" . $role_id . "' and r.user_id='" . $this->user_id . "' order by p.id desc");
            if (empty($project)) {
                $newdata = array('result' => 'success', 'response' => array('message' => "Not any Projects"));
                $this->response($newdata);
            }

            $project_all = array();
            $i = 0;
            foreach ($project as $key => $value) {
                $project_all[$i]['id'] = $value->id;
                $project_all[$i]['name'] = $value->name;
                $project_all[$i]['reference'] = $value->reference;
                if (!empty($value->description)) {
                    $project_all[$i]['description'] = strip_tags($value->description);
                } else {
                    $project_all[$i]['description'] = "";
                }
                $project_all[$i]['start'] = $value->start;
                $project_all[$i]['end'] = $value->end;
                $project_all[$i]['company_name'] = $get_compnay->name;
                $project_all[$i]['progress'] = $value->progress;
                $project_all[$i]['phases'] = $value->phases;
                $project_all[$i]['reference'] = $value->reference;
                if ($value->tracking == '') {
                    $project_all[$i]['tracking'] = 0;
                } else {
                    $project_all[$i]['tracking'] = $value->tracking;
                }
                $project_all[$i]['datetime'] = $value->datetime;
                $project_all[$i]['company_id'] = $value->company_id;
                $i++;
            }
            if (empty($project_all)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $project_all, 'code' => 200);
            $this->response($newdata);
        }
        if ($status == 'closed') {
            if ($role_id == 2) {
                $project = Project::find_by_sql('SELECT DISTINCT (p.id),p.* from projects p left join user_roles r on p.company_id=r.company_id where p.company_id="' . $company_id . '" and r.role_id="' . $role_id . '" and r.user_id="' . $this->user_id . '" and ' . $options_closed . '');
                if (empty($project)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any Projects"));
                    $this->response($newdata);
                }

                $project_all = array();
                $i = 0;
                foreach ($project as $key => $value) {
                    $project_all[$i]['id'] = $value->id;
                    $project_all[$i]['name'] = $value->name;
                    $project_all[$i]['reference'] = $value->reference;
                    if (!empty($value->description)) {
                        $project_all[$i]['description'] = strip_tags($value->description);
                    } else {
                        $project_all[$i]['description'] = "";
                    }
                    $project_all[$i]['start'] = $value->start;
                    $project_all[$i]['end'] = $value->end;
                    $project_all[$i]['company_name'] = $get_compnay->name;
                    $project_all[$i]['progress'] = $value->progress;
                    $project_all[$i]['phases'] = $value->phases;
                    $project_all[$i]['reference'] = $value->reference;
                    if ($value->tracking == '') {
                        $project_all[$i]['tracking'] = 0;
                    } else {
                        $project_all[$i]['tracking'] = $value->tracking;
                    }
                    $project_all[$i]['datetime'] = $value->datetime;
                    $project_all[$i]['company_id'] = $value->company_id;

                    $assign_client_details = $this->db->query('SELECT DISTINCT (p.assign_user_id), u.* FROM project_assign_clients p
                                                            LEFT JOIN user_roles r ON p.company_id = r.company_id
                                                            LEFT JOIN users u ON p.assign_user_id = u.id
                                                            WHERE p.project_id = "' . $value->id . '"
                                                            AND r.company_id = "' . $company_id . '" AND r.role_id="' . $role_id . '" and u.status="active"')->result_array();
                    if (empty($assign_client_details)) {
                        $project_all[$i]['clients'] = "";
                    } else {
                        $j = 0;
                        foreach ($assign_client_details as $key1 => $value1) {
                            $project_all[$i]['clients'][$j]['user_id'] = $value1['assign_user_id'];
                            $project_all[$i]['clients'][$j]['firstname'] = $value1['firstname'];
                            $project_all[$i]['clients'][$j]['lastname'] = $value1['lastname'];
                            $project_all[$i]['clients'][$j]['email'] = $value1['email'];
                            $project_all[$i]['clients'][$j]['userpic'] = $value1['userpic'];
                            $j++;
                        }
                    }
                    $i++;
                }
                if (empty($project_all)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $project_all, 'code' => 200);
                $this->response($newdata);
            }

            $project = Project::find_by_sql("SELECT p.* from projects p join project_assign_clients c on p.id=c.project_id and p.company_id=c.company_id left join user_roles r on c.company_id=r.company_id and c.assign_user_id=r.user_id where " . $options_closed . " and r.company_id='" . $company_id . "' and r.role_id='" . $role_id . "' and r.user_id='" . $this->user_id . "' order by p.id desc");
            if (empty($project)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any Projects"));
                $this->response($newdata);
            }

            $project_all = array();
            $i = 0;
            foreach ($project as $key => $value) {
                $project_all[$i]['id'] = $value->id;
                $project_all[$i]['name'] = $value->name;
                $project_all[$i]['reference'] = $value->reference;
                if (!empty($value->description)) {
                    $project_all[$i]['description'] = strip_tags($value->description);
                } else {
                    $project_all[$i]['description'] = "";
                }
                //$project_all[$i]['description'] = $value->description;
                $project_all[$i]['start'] = $value->start;
                $project_all[$i]['end'] = $value->end;
                $project_all[$i]['company_name'] = $get_compnay->name;
                $project_all[$i]['progress'] = $value->progress;
                $project_all[$i]['phases'] = $value->phases;
                $project_all[$i]['reference'] = $value->reference;
                if ($value->tracking == '') {
                    $project_all[$i]['tracking'] = 0;
                } else {
                    $project_all[$i]['tracking'] = $value->tracking;
                }
                $project_all[$i]['datetime'] = $value->datetime;
                $project_all[$i]['company_id'] = $value->company_id;
                $i++;
            }
            if (empty($project_all)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $project_all, 'code' => 200);
            $this->response($newdata);
        }

        $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
        $this->response($newdata);
    }

    /* All Tasks Lists By Project */

    function task_lists_by_project() {

        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $status = trim(htmlspecialchars($_REQUEST['status'])) ? trim(htmlspecialchars($_REQUEST['status'])) : '';
        if (empty($status) || empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                        LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];

            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $get_compnay = Company::find_by_id($company_id);
                $options_done = 't.status = "done"';
                $options_open = 't.status ="open"';
                if ($status == 'all') {
                    $task_list = ProjectHasTask::find_by_sql("SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.project_id = '" . $project_id . "' AND r.company_id = '" . $company_id . "' AND r.role_id ='" . $role_id . "' and r.user_id='" . $this->user_id . "'UNION
                                    SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
                                    join user_roles r on at.assign_user_id=r.user_id where r.company_id='" . $company_id . "' and r.role_id='" . $role_id . "' and r.user_id='" . $this->user_id . "' AND t.project_id = '" . $project_id . "'
                                ");
                    if (empty($task_list)) {
                        $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any tasks yet"));
                        $this->response($newdata);
                    }
                    //else {
                    $newArr = array();
                    $i = 0;
                    foreach ($task_list as $key => $value) {
                        $newArr[$i]['id'] = $value->id;
                        $newArr[$i]['user_id'] = $value->user_id;
                        $newArr[$i]['task_name'] = $value->name;
                        $newArr[$i]['status'] = $value->status;
                        $newArr[$i]['priority'] = $value->priority;
                        $newArr[$i]['public'] = $value->public;
                        if ($value->datetime == '') {
                            $newArr[$i]['datetime'] = 0;
                        } else {
                            $newArr[$i]['datetime'] = $value->datetime;
                        }
                        $newArr[$i]['due_date'] = $value->due_date;
                        if (!empty($value->description)) {
                            $newArr[$i]['description'] = strip_tags($value->description);
                        } else {
                            $newArr[$i]['description'] = "";
                        }
                        //$newArr[$i]['description'] = $value->description;
                        $newArr[$i]['value'] = $value->value;
                        if ($value->tracking == '') {
                            $newArr[$i]['tracking'] = 0;
                        } else {
                            $newArr[$i]['tracking'] = $value->tracking;
                        }
                        if ($value->milestone_order == '') {
                            $newArr[$i]['milestone_order'] = 0;
                        } else {
                            $newArr[$i]['milestone_order'] = $value->milestone_order;
                        }
                        if ($value->task_order == '') {
                            $newArr[$i]['task_order'] = 0;
                        } else {
                            $newArr[$i]['task_order'] = $value->task_order;
                        }
                        $newArr[$i]['time_spent'] = $value->time_spent;
                        $newArr[$i]['milestone_id'] = $value->milestone_id;
                        $newArr[$i]['progress'] = $value->progress;
                        $newArr[$i]['created_at'] = $value->created_at;


                        $get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                        //var_dump(expression)

                        if (empty($get_assign_clients)) {
                            $newArr[$i]['clients'] = "";
                        } else {
                            $j = 0;
                            foreach ($get_assign_clients as $k => $v) {
                                $get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                                if (!empty($get_client_details)) {
                                    $newArr[$i]['clients'][$j]['firstname'] = $get_client_details['firstname'];
                                    $newArr[$i]['clients'][$j]['lastname'] = $get_client_details['lastname'];
                                    $newArr[$i]['clients'][$j]['email'] = $get_client_details['email'];
                                    $newArr[$i]['clients'][$j]['userpic'] = $get_client_details['userpic'];
                                    $j++;
                                }
                            }
                        }
                        $i++;
                    }
                    //echo "<pre>";print_r($newArr);exit;
                    if (empty($newArr)) {
                        $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                        $this->response($newdata);
                    }

                    $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                    $this->response($newdata);
                }
                if ($status == 'open') {

                    $task_list = ProjectHasTask::find_by_sql('SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE ' . $options_open . ' and r.company_id = "' . $company_id . '" AND r.role_id ="' . $role_id . '" and r.user_id="' . $this->user_id . '" AND t.project_id="' . $project_id . '"
                                    UNION
                                    SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
                                    join user_roles r on at.assign_user_id=r.user_id where ' . $options_open . ' and  r.company_id="' . $company_id . '" and r.role_id="' . $role_id . '" and r.user_id="' . $this->user_id . '" AND t.project_id="' . $project_id . '"
                                    ');
                    if (empty($task_list)) {
                        $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any tasks yet"));
                        $this->response($newdata);
                    }
                    //else {
                    $newArr = array();
                    $i = 0;
                    foreach ($task_list as $key => $value) {
                        $newArr[$i]['id'] = $value->id;
                        $newArr[$i]['user_id'] = $value->user_id;
                        $newArr[$i]['task_name'] = $value->name;
                        $newArr[$i]['status'] = $value->status;
                        $newArr[$i]['priority'] = $value->priority;
                        $newArr[$i]['public'] = $value->public;
                        if ($value->datetime == '') {
                            $newArr[$i]['datetime'] = 0;
                        } else {
                            $newArr[$i]['datetime'] = $value->datetime;
                        }
                        $newArr[$i]['due_date'] = $value->due_date;
                        if (!empty($value->description)) {
                            $newArr[$i]['description'] = strip_tags($value->description);
                        } else {
                            $newArr[$i]['description'] = "";
                        }
                        //$newArr[$i]['description'] = $value->description;
                        $newArr[$i]['value'] = $value->value;
                        if ($value->tracking == '') {
                            $newArr[$i]['tracking'] = 0;
                        } else {
                            $newArr[$i]['tracking'] = $value->tracking;
                        }
                        if ($value->milestone_order == '') {
                            $newArr[$i]['milestone_order'] = 0;
                        } else {
                            $newArr[$i]['milestone_order'] = $value->milestone_order;
                        }
                        if ($value->task_order == '') {
                            $newArr[$i]['task_order'] = 0;
                        } else {
                            $newArr[$i]['task_order'] = $value->task_order;
                        }
                        $newArr[$i]['time_spent'] = $value->time_spent;
                        $newArr[$i]['milestone_id'] = $value->milestone_id;
                        $newArr[$i]['progress'] = $value->progress;
                        $newArr[$i]['created_at'] = $value->created_at;


                        $get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                        //var_dump(expression)

                        if (empty($get_assign_clients)) {
                            $newArr[$i]['clients'] = "";
                        } else {
                            $j = 0;
                            foreach ($get_assign_clients as $k => $v) {
                                $get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                                if (!empty($get_client_details)) {
                                    $newArr[$i]['clients'][$j]['firstname'] = $get_client_details['firstname'];
                                    $newArr[$i]['clients'][$j]['lastname'] = $get_client_details['lastname'];
                                    $newArr[$i]['clients'][$j]['email'] = $get_client_details['email'];
                                    $newArr[$i]['clients'][$j]['userpic'] = $get_client_details['userpic'];
                                    $j++;
                                }
                            }
                        }
                        $i++;
                    }
                    //echo "<pre>";print_r($newArr);exit;
                    if (empty($newArr)) {
                        $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                        $this->response($newdata);
                    }
                    //else {
                    $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                    $this->response($newdata);
                    //}
                    //}
                }
                if ($status == 'done') {
                    $task_list = ProjectHasTask::find_by_sql('SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE ' . $options_done . ' and r.company_id = "' . $company_id . '" AND r.role_id ="' . $role_id . '" and r.user_id="' . $this->user_id . '" AND t.project_id="' . $project_id . '"
                                UNION
                                SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
                                join user_roles r on at.assign_user_id=r.user_id where ' . $options_done . ' and  r.company_id="' . $company_id . '" and r.role_id="' . $role_id . '" and r.user_id="' . $this->user_id . '" AND t.project_id="' . $project_id . '"
                                ');
                    if (empty($task_list)) {
                        $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any tasks yet"));
                        $this->response($newdata);
                    }
                    //else {
                    $newArr = array();
                    $i = 0;
                    foreach ($task_list as $key => $value) {
                        $newArr[$i]['id'] = $value->id;
                        $newArr[$i]['user_id'] = $value->user_id;
                        $newArr[$i]['task_name'] = $value->name;
                        $newArr[$i]['status'] = $value->status;
                        $newArr[$i]['priority'] = $value->priority;
                        $newArr[$i]['public'] = $value->public;
                        if ($value->datetime == '') {
                            $newArr[$i]['datetime'] = 0;
                        } else {
                            $newArr[$i]['datetime'] = $value->datetime;
                        }
                        $newArr[$i]['due_date'] = $value->due_date;
                        if (!empty($value->description)) {
                            $newArr[$i]['description'] = strip_tags($value->description);
                        } else {
                            $newArr[$i]['description'] = "";
                        }
                        //$newArr[$i]['description'] = $value->description;
                        $newArr[$i]['value'] = $value->value;
                        if ($value->tracking == '') {
                            $newArr[$i]['tracking'] = 0;
                        } else {
                            $newArr[$i]['tracking'] = $value->tracking;
                        }
                        if ($value->milestone_order == '') {
                            $newArr[$i]['milestone_order'] = 0;
                        } else {
                            $newArr[$i]['milestone_order'] = $value->milestone_order;
                        }
                        if ($value->task_order == '') {
                            $newArr[$i]['task_order'] = 0;
                        } else {
                            $newArr[$i]['task_order'] = $value->task_order;
                        }
                        $newArr[$i]['time_spent'] = $value->time_spent;
                        $newArr[$i]['milestone_id'] = $value->milestone_id;
                        $newArr[$i]['progress'] = $value->progress;
                        $newArr[$i]['created_at'] = $value->created_at;


                        $get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                        //var_dump(expression)

                        if (empty($get_assign_clients)) {
                            $newArr[$i]['clients'] = "";
                        } else {
                            $j = 0;
                            foreach ($get_assign_clients as $k => $v) {
                                $get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                                if (!empty($get_client_details)) {
                                    $newArr[$i]['clients'][$j]['firstname'] = $get_client_details['firstname'];
                                    $newArr[$i]['clients'][$j]['lastname'] = $get_client_details['lastname'];
                                    $newArr[$i]['clients'][$j]['email'] = $get_client_details['email'];
                                    $newArr[$i]['clients'][$j]['userpic'] = $get_client_details['userpic'];
                                    $j++;
                                }
                            }
                        }
                        $i++;
                    }
                    //echo "<pre>";print_r($newArr);exit;
                    if (empty($newArr)) {
                        $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                        $this->response($newdata);
                    }
                    //else {
                    $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                    $this->response($newdata);
                    //}
                    //}
                }

                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $company_id . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Project Not Assign to You'));
                $this->response($newdata);
            }
            $get_compnay = Company::find_by_id($company_id);
            $options_done = 't.status = "done"';
            $options_open = 't.status ="open"';
            if ($status == 'all') {
                $task_list = ProjectHasTask::find_by_sql("SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.project_id = '" . $project_id . "' AND r.company_id = '" . $company_id . "' AND r.role_id ='" . $role_id . "' and r.user_id='" . $this->user_id . "'UNION
                                    SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
                                    join user_roles r on at.assign_user_id=r.user_id where r.company_id='" . $company_id . "' and r.role_id='" . $role_id . "' and r.user_id='" . $this->user_id . "' AND t.project_id = '" . $project_id . "'
                                ");
                if (empty($task_list)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any tasks yet"));
                    $this->response($newdata);
                }
                //else {
                $newArr = array();
                $i = 0;
                foreach ($task_list as $key => $value) {
                    $newArr[$i]['id'] = $value->id;
                    $newArr[$i]['user_id'] = $value->user_id;
                    $newArr[$i]['task_name'] = $value->name;
                    $newArr[$i]['status'] = $value->status;
                    $newArr[$i]['priority'] = $value->priority;
                    $newArr[$i]['public'] = $value->public;
                    if ($value->datetime == '') {
                        $newArr[$i]['datetime'] = 0;
                    } else {
                        $newArr[$i]['datetime'] = $value->datetime;
                    }
                    $newArr[$i]['due_date'] = $value->due_date;
                    if (!empty($value->description)) {
                        $newArr[$i]['description'] = strip_tags($value->description);
                    } else {
                        $newArr[$i]['description'] = "";
                    }
                    //$newArr[$i]['description'] = $value->description;
                    $newArr[$i]['value'] = $value->value;
                    if ($value->tracking == '') {
                        $newArr[$i]['tracking'] = 0;
                    } else {
                        $newArr[$i]['tracking'] = $value->tracking;
                    }
                    if ($value->milestone_order == '') {
                        $newArr[$i]['milestone_order'] = 0;
                    } else {
                        $newArr[$i]['milestone_order'] = $value->milestone_order;
                    }
                    if ($value->task_order == '') {
                        $newArr[$i]['task_order'] = 0;
                    } else {
                        $newArr[$i]['task_order'] = $value->task_order;
                    }
                    $newArr[$i]['time_spent'] = $value->time_spent;
                    $newArr[$i]['milestone_id'] = $value->milestone_id;
                    $newArr[$i]['progress'] = $value->progress;
                    $newArr[$i]['created_at'] = $value->created_at;


                    $get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                    //var_dump(expression)

                    if (empty($get_assign_clients)) {
                        $newArr[$i]['clients'] = "";
                    } else {
                        $j = 0;
                        foreach ($get_assign_clients as $k => $v) {
                            $get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                            if (!empty($get_client_details)) {
                                $newArr[$i]['clients'][$j]['firstname'] = $get_client_details['firstname'];
                                $newArr[$i]['clients'][$j]['lastname'] = $get_client_details['lastname'];
                                $newArr[$i]['clients'][$j]['email'] = $get_client_details['email'];
                                $newArr[$i]['clients'][$j]['userpic'] = $get_client_details['userpic'];
                                $j++;
                            }
                        }
                    }
                    $i++;
                }
                //echo "<pre>";print_r($newArr);exit;
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }
                //else {
                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
                //}
                //}
            }
            if ($status == 'open') {

                $task_list = ProjectHasTask::find_by_sql('SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE ' . $options_open . ' and r.company_id = "' . $company_id . '" AND r.role_id ="' . $role_id . '" and r.user_id="' . $this->user_id . '" AND t.project_id="' . $project_id . '"
                                    UNION
                                    SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
                                    join user_roles r on at.assign_user_id=r.user_id where ' . $options_open . ' and  r.company_id="' . $company_id . '" and r.role_id="' . $role_id . '" and r.user_id="' . $this->user_id . '" AND t.project_id="' . $project_id . '"
                                    ');
                if (empty($task_list)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any tasks yet"));
                    $this->response($newdata);
                }
                //else {
                $newArr = array();
                $i = 0;
                foreach ($task_list as $key => $value) {
                    $newArr[$i]['id'] = $value->id;
                    $newArr[$i]['user_id'] = $value->user_id;
                    $newArr[$i]['task_name'] = $value->name;
                    $newArr[$i]['status'] = $value->status;
                    $newArr[$i]['priority'] = $value->priority;
                    $newArr[$i]['public'] = $value->public;
                    if ($value->datetime == '') {
                        $newArr[$i]['datetime'] = 0;
                    } else {
                        $newArr[$i]['datetime'] = $value->datetime;
                    }
                    $newArr[$i]['due_date'] = $value->due_date;
                    if (!empty($value->description)) {
                        $newArr[$i]['description'] = strip_tags($value->description);
                    } else {
                        $newArr[$i]['description'] = "";
                    }
                    //$newArr[$i]['description'] = $value->description;
                    $newArr[$i]['value'] = $value->value;
                    if ($value->tracking == '') {
                        $newArr[$i]['tracking'] = 0;
                    } else {
                        $newArr[$i]['tracking'] = $value->tracking;
                    }
                    if ($value->milestone_order == '') {
                        $newArr[$i]['milestone_order'] = 0;
                    } else {
                        $newArr[$i]['milestone_order'] = $value->milestone_order;
                    }
                    if ($value->task_order == '') {
                        $newArr[$i]['task_order'] = 0;
                    } else {
                        $newArr[$i]['task_order'] = $value->task_order;
                    }
                    $newArr[$i]['time_spent'] = $value->time_spent;
                    $newArr[$i]['milestone_id'] = $value->milestone_id;
                    $newArr[$i]['progress'] = $value->progress;
                    $newArr[$i]['created_at'] = $value->created_at;


                    $get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                    //var_dump(expression)

                    if (empty($get_assign_clients)) {
                        $newArr[$i]['clients'] = "";
                    } else {
                        $j = 0;
                        foreach ($get_assign_clients as $k => $v) {
                            $get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                            if (!empty($get_client_details)) {
                                $newArr[$i]['clients'][$j]['firstname'] = $get_client_details['firstname'];
                                $newArr[$i]['clients'][$j]['lastname'] = $get_client_details['lastname'];
                                $newArr[$i]['clients'][$j]['email'] = $get_client_details['email'];
                                $newArr[$i]['clients'][$j]['userpic'] = $get_client_details['userpic'];
                                $j++;
                            }
                        }
                    }
                    $i++;
                }
                //echo "<pre>";print_r($newArr);exit;
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }
                //else {
                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
                //}
                //}
            }
            if ($status == 'done') {
                $task_list = ProjectHasTask::find_by_sql('SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE ' . $options_done . ' and r.company_id = "' . $company_id . '" AND r.role_id ="' . $role_id . '" and r.user_id="' . $this->user_id . '" AND t.project_id="' . $project_id . '"
                                UNION
                                SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
                                join user_roles r on at.assign_user_id=r.user_id where ' . $options_done . ' and  r.company_id="' . $company_id . '" and r.role_id="' . $role_id . '" and r.user_id="' . $this->user_id . '" AND t.project_id="' . $project_id . '"
                                ');
                if (empty($task_list)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any tasks yet"));
                    $this->response($newdata);
                }
                //else {
                $newArr = array();
                $i = 0;
                foreach ($task_list as $key => $value) {
                    $newArr[$i]['id'] = $value->id;
                    $newArr[$i]['user_id'] = $value->user_id;
                    $newArr[$i]['task_name'] = $value->name;
                    $newArr[$i]['status'] = $value->status;
                    $newArr[$i]['priority'] = $value->priority;
                    $newArr[$i]['public'] = $value->public;
                    if ($value->datetime == '') {
                        $newArr[$i]['datetime'] = 0;
                    } else {
                        $newArr[$i]['datetime'] = $value->datetime;
                    }
                    $newArr[$i]['due_date'] = $value->due_date;
                    if (!empty($value->description)) {
                        $newArr[$i]['description'] = strip_tags($value->description);
                    } else {
                        $newArr[$i]['description'] = "";
                    }
                    //$newArr[$i]['description'] = $value->description;
                    $newArr[$i]['value'] = $value->value;
                    if ($value->tracking == '') {
                        $newArr[$i]['tracking'] = 0;
                    } else {
                        $newArr[$i]['tracking'] = $value->tracking;
                    }
                    if ($value->milestone_order == '') {
                        $newArr[$i]['milestone_order'] = 0;
                    } else {
                        $newArr[$i]['milestone_order'] = $value->milestone_order;
                    }
                    if ($value->task_order == '') {
                        $newArr[$i]['task_order'] = 0;
                    } else {
                        $newArr[$i]['task_order'] = $value->task_order;
                    }
                    $newArr[$i]['time_spent'] = $value->time_spent;
                    $newArr[$i]['milestone_id'] = $value->milestone_id;
                    $newArr[$i]['progress'] = $value->progress;
                    $newArr[$i]['created_at'] = $value->created_at;


                    $get_assign_clients = $this->db->query('select assign_user_id from project_assign_tasks where task_id="' . $value->id . '"')->result_array();
                    //var_dump(expression)

                    if (empty($get_assign_clients)) {
                        $newArr[$i]['clients'] = "";
                    } else {
                        $j = 0;
                        foreach ($get_assign_clients as $k => $v) {
                            $get_client_details = $this->db->query('select firstname,lastname,email,userpic from users where id="' . $v['assign_user_id'] . '"')->row_array();
                            if (!empty($get_client_details)) {
                                $newArr[$i]['clients'][$j]['firstname'] = $get_client_details['firstname'];
                                $newArr[$i]['clients'][$j]['lastname'] = $get_client_details['lastname'];
                                $newArr[$i]['clients'][$j]['email'] = $get_client_details['email'];
                                $newArr[$i]['clients'][$j]['userpic'] = $get_client_details['userpic'];
                                $j++;
                            }
                        }
                    }
                    $i++;
                }
                //echo "<pre>";print_r($newArr);exit;
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }
                //else {
                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
                //}
                //}
            }

            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* All Project Activities */

    function allprojectactivities() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                        LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
        if (empty($get_data)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
            $this->response($newdata);
        }
        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if (is_numeric($project_id)) {
            $company_id = $get_data['company_id'];
            if ($get_data['role_id'] == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $activity = $this->db->query('SELECT id,project_id,user_id,datetime,subject,message,type from  project_has_activities where project_id="' . $project_id . '" and user_id="' . $this->user_id . '" ')->result_array();

                if (empty($activity)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any activities for this project yet"));
                    $this->response($newdata);
                }

                $newArr = array();
                $i = 0;
                foreach ($activity as $key => $value) {
                    $newArr[$i]['project_id'] = $value['project_id'];
                    $newArr[$i]['user_id'] = $value['user_id'];
                    $newArr[$i]['datetime'] = $value['datetime'];
                    $newArr[$i]['subject'] = $value['subject'];
                    if ($value['message'] == "") {
                        $newArr[$i]['message'] = "";
                    } else {
                        $newArr[$i]['message'] = strip_tags($value['message']);
                    }
                    $newArr[$i]['type'] = $value['type'];
                    $i++;
                }
                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $company_id . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $activity = $this->db->query('SELECT id,project_id,user_id,datetime,subject,message,type from  project_has_activities where project_id="' . $project_id . '"')->result_array();
            if (empty($activity)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => "Not any activities for this project yet"));
                $this->response($newdata);
            }

            $newArr = array();
            $i = 0;
            foreach ($activity as $key => $value) {
                $newArr[$i]['project_id'] = $value['project_id'];
                $newArr[$i]['user_id'] = $value['user_id'];
                $newArr[$i]['datetime'] = $value['datetime'];
                $newArr[$i]['subject'] = $value['subject'];
                if ($value['message'] == "") {
                    $newArr[$i]['message'] = "";
                } else {
                    $newArr[$i]['message'] = strip_tags($value['message']);
                }
                $newArr[$i]['type'] = $value['type'];
                $i++;
            }
            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
            $this->response($newdata);
        }

        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* All Tasks Time By Project */

    function alltaskstimebyproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
          LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $company_id = $get_data['company_id'];
            if ($get_data['role_id'] == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $alltasktime = Project::getAllTasksTime($project_id);
                if (empty($alltasktime)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $all_arr = explode(' ', $alltasktime);
                $newdata = array('result' => 'success', 'response' => array('hours' => $all_arr[0], 'minutes' => $all_arr[2]));
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $alltasktime = Project::getAllTasksTime($project_id);
            if (empty($alltasktime)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }
            $all_arr = explode(' ', $alltasktime);
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'hours' => $all_arr[0], 'minutes' => $all_arr[2]));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* All Tasks Attachements By Project */

    function alltaskattachmentsbyproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
          LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $company_id = $get_data['company_id'];
            if ($get_data['role_id'] == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $get_project_details = Project::find_by_id($project_id);
                if (empty($get_project_details)) {
                    $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                    $this->response($newdata);
                }

                $company_id = $get_project_details->company_id;
                $alltaskattachment = $this->db->query('SELECT * from  project_has_tasks_attachment where project_id="' . $project_id . '" and company_id="' . $company_id . '"')->result_array();
                if (empty($alltaskattachment)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Tasks Attachment yet'));
                    $this->response($newdata);
                }

                $newArr = array();
                $i = 0;
                foreach ($alltaskattachment as $key => $value) {
                    $newArr[$i]['id'] = $value['id'];
                    $newArr[$i]['project_name'] = $get_project_details->name;
                    $newArr[$i]['user_id'] = $value['user_id'];
                    $get_user = User::find($newArr[$i]['user_id']);
                    $newArr[$i]['user'] = $get_user->firstname . " " . $get_user->lastname;
                    $newArr[$i]['task_id'] = $value['task_id'];
                    $get_task_name = ProjectHasTask::find_by_id($newArr[$i]['task_id']);
                    $newArr[$i]['task_name'] = $get_task_name->name;
                    if (!empty($value['task_attach_file'])) {
                        $path = FCPATH . 'files/tasks_attachment/' . $value['task_attach_file'];
                        if (file_exists($path)) {
                            $attachment_url = site_url() . 'files/tasks_attachment/' . $value['task_attach_file'];
                            $newArr[$i]['attachment'] = $attachment_url;
                        } else {
                            $newArr[$i]['attachment'] = "File not Found";
                        }
                    } else {
                        $newArr[$i]['attachment'] = "No Attachement for this" . $newArr[$i]['task_name'];
                    }
                    $i++;
                }
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $get_project_details = Project::find_by_id($project_id);
            if (empty($get_project_details)) {
                $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                $this->response($newdata);
            }

            $company_id = $get_project_details->company_id;
            $alltaskattachment = $this->db->query('SELECT * from  project_has_tasks_attachment where project_id="' . $project_id . '" and company_id="' . $company_id . '"')->result_array();
            if (empty($alltaskattachment)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Tasks Attachment yet'));
                $this->response($newdata);
            }

            $newArr = array();
            $i = 0;
            foreach ($alltaskattachment as $key => $value) {
                $newArr[$i]['id'] = $value['id'];
                $newArr[$i]['project_name'] = $get_project_details->name;
                $newArr[$i]['user_id'] = $value['user_id'];
                $get_user = User::find($newArr[$i]['user_id']);
                $newArr[$i]['user'] = $get_user->firstname . " " . $get_user->lastname;
                $newArr[$i]['task_id'] = $value['task_id'];
                $get_task_name = ProjectHasTask::find_by_id($newArr[$i]['task_id']);
                $newArr[$i]['task_name'] = $get_task_name->name;
                if (!empty($value['task_attach_file'])) {
                    $path = FCPATH . 'files/tasks_attachment/' . $value['task_attach_file'];
                    if (file_exists($path)) {
                        $attachment_url = site_url() . 'files/tasks_attachment/' . $value['task_attach_file'];
                        $newArr[$i]['attachment'] = $attachment_url;
                    } else {
                        $newArr[$i]['attachment'] = "File not Found";
                    }
                } else {
                    $newArr[$i]['attachment'] = "No Attachement for this" . $newArr[$i]['task_name'];
                }
                $i++;
            }
            if (empty($newArr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* All MilestonesAttachments By Project */

    function allmilestoneattachmentsbyproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
          LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $company_id = $get_data['company_id'];
            if ($get_data['role_id'] == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $get_project_details = Project::find_by_id($project_id);
                if (empty($get_project_details)) {
                    $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                    $this->response($newdata);
                }

                $company_id = $get_project_details->company_id;
                $allmilestoneattachment = $this->db->query('SELECT * from  project_has_milestones_attachment where project_id="' . $project_id . '" and company_id="' . $company_id . '"')->result_array();
                if (empty($allmilestoneattachment)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Milestone Attachment yet'));
                    $this->response($newdata);
                }

                $newArr = array();
                $i = 0;
                foreach ($allmilestoneattachment as $key => $value) {
                    $newArr[$i]['id'] = $value['id'];
                    $newArr[$i]['project_name'] = $get_project_details->name;
                    $newArr[$i]['user_id'] = $value['user_id'];
                    $get_user = User::find($newArr[$i]['user_id']);
                    $newArr[$i]['user'] = $get_user->firstname . " " . $get_user->lastname;
                    $newArr[$i]['milestone_id'] = $value['milestone_id'];
                    $get_milestone_name = ProjectHasMilestone::find_by_id($newArr[$i]['milestone_id']);
                    $newArr[$i]['milestone_name'] = $get_milestone_name->name;
                    if (!empty($value['milestone_attach_file'])) {
                        $path = FCPATH . 'files/milestone_attachment/' . $value['milestone_attach_file'];
                        if (file_exists($path)) {
                            $attachment_url = site_url() . 'files/milestone_attachment/' . $value['milestone_attach_file'];
                            $newArr[$i]['attachment'] = $attachment_url;
                        } else {
                            $newArr[$i]['attachment'] = "File not Found";
                        }
                    } else {
                        $newArr[$i]['attachment'] = "No Attachement for this" . $newArr[$i]['milestone_name'];
                    }
                    $i++;
                }
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $get_project_details = Project::find_by_id($project_id);
            if (empty($get_project_details)) {
                $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                $this->response($newdata);
            }

            $company_id = $get_project_details->company_id;
            $allmilestoneattachment = $this->db->query('SELECT * from  project_has_milestones_attachment where project_id="' . $project_id . '" and company_id="' . $company_id . '"')->result_array();
            if (empty($allmilestoneattachment)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Milestone Attachment yet'));
                $this->response($newdata);
            }

            $newArr = array();
            $i = 0;
            foreach ($allmilestoneattachment as $key => $value) {
                $newArr[$i]['id'] = $value['id'];
                $newArr[$i]['project_name'] = $get_project_details->name;
                $newArr[$i]['user_id'] = $value['user_id'];
                $get_user = User::find($newArr[$i]['user_id']);
                $newArr[$i]['user'] = $get_user->firstname . " " . $get_user->lastname;
                $newArr[$i]['milestone_id'] = $value['milestone_id'];
                $get_milestone_name = ProjectHasMilestone::find_by_id($newArr[$i]['milestone_id']);
                $newArr[$i]['milestone_name'] = $get_milestone_name->name;
                if (!empty($value['milestone_attach_file'])) {
                    $path = FCPATH . 'files/milestone_attachment/' . $value['milestone_attach_file'];
                    if (file_exists($path)) {
                        $attachment_url = site_url() . 'files/milestone_attachment/' . $value['milestone_attach_file'];
                        $newArr[$i]['attachment'] = $attachment_url;
                    } else {
                        $newArr[$i]['attachment'] = "File not Found";
                    }
                } else {
                    $newArr[$i]['attachment'] = "No Attachement for this" . $newArr[$i]['milestone_name'];
                }
                $i++;
            }
            if (empty($newArr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* All TasksComments By Project */

    function alltaskcommetsbyproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
          LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $company_id = $get_data['company_id'];
            if ($get_data['role_id'] == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $get_project_details = Project::find_by_id($project_id);
                if (empty($get_project_details)) {
                    $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                    $this->response($newdata);
                }

                $company_id = $get_project_details->company_id;
                $alltaskcomment = $this->db->query('SELECT * from  project_has_tasks_comment where project_id="' . $project_id . '" and company_id="' . $company_id . '"')->result_array();
                if (empty($alltaskcomment)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Tasks comments yet'));
                    $this->response($newdata);
                }

                $newArr = array();
                $i = 0;
                foreach ($alltaskcomment as $key => $value) {
                    $newArr[$i]['id'] = $value['id'];
                    $newArr[$i]['project_name'] = $get_project_details->name;
                    $newArr[$i]['user_id'] = $value['user_id'];
                    $get_user = User::find($newArr[$i]['user_id']);
                    $newArr[$i]['user'] = $get_user->firstname . " " . $get_user->lastname;
                    $newArr[$i]['task_id'] = $value['task_id'];
                    $get_task_name = ProjectHasTask::find_by_id($newArr[$i]['task_id']);
                    $newArr[$i]['task_name'] = $get_task_name->name;
                    if (!empty($value['message'])) {
                        $newArr[$i]['message'] = strip_tags($value['message']);
                    } else {
                        $newArr[$i]['message'] = "No message for this" . $newArr[$i]['task_name'];
                    }
                    $i++;
                }
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $get_project_details = Project::find_by_id($project_id);
            if (empty($get_project_details)) {
                $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                $this->response($newdata);
            }

            $company_id = $get_project_details->company_id;
            $alltaskcomment = $this->db->query('SELECT * from  project_has_tasks_comment where project_id="' . $project_id . '" and company_id="' . $company_id . '"')->result_array();
            if (empty($alltaskcomment)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Tasks comments yet'));
                $this->response($newdata);
            }

            $newArr = array();
            $i = 0;
            foreach ($alltaskcomment as $key => $value) {
                $newArr[$i]['id'] = $value['id'];
                $newArr[$i]['project_name'] = $get_project_details->name;
                $newArr[$i]['user_id'] = $value['user_id'];
                $get_user = User::find($newArr[$i]['user_id']);
                $newArr[$i]['user'] = $get_user->firstname . " " . $get_user->lastname;
                $newArr[$i]['task_id'] = $value['task_id'];
                $get_task_name = ProjectHasTask::find_by_id($newArr[$i]['task_id']);
                $newArr[$i]['task_name'] = $get_task_name->name;
                if (!empty($value['message'])) {
                    $newArr[$i]['message'] = strip_tags($value['message']);
                } else {
                    $newArr[$i]['message'] = "No message for this" . $newArr[$i]['task_name'];
                }
                $i++;
            }
            if (empty($newArr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
            $this->response($newdata);
        }

        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* All Milestones Comments By Project */

    function allmilestonecommetsbyproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
          LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $company_id = $get_data['company_id'];
            if ($get_data['role_id'] == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $get_project_details = Project::find_by_id($project_id);
                if (empty($get_project_details)) {
                    $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                    $this->response($newdata);
                }

                $allmilestonecomment = $this->db->query('SELECT * from  project_has_milestones_comment where project_id="' . $project_id . '" and company_id="' . $company_id . '"')->result_array();
                if (empty($allmilestonecomment)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Milestones comments yet'));
                    $this->response($newdata);
                }

                $newArr = array();
                $i = 0;
                foreach ($allmilestonecomment as $key => $value) {
                    $newArr[$i]['id'] = $value['id'];
                    $newArr[$i]['project_name'] = $get_project_details->name;
                    $newArr[$i]['user_id'] = $value['user_id'];
                    $get_user = User::find($newArr[$i]['user_id']);
                    $newArr[$i]['user'] = $get_user->firstname . " " . $get_user->lastname;
                    $newArr[$i]['milestone_id'] = $value['milestone_id'];
                    $get_milestone_name = ProjectHasMilestone::find_by_id($newArr[$i]['milestone_id']);
                    $newArr[$i]['milestone_name'] = $get_milestone_name->name;
                    if (!empty($value['message'])) {
                        $newArr[$i]['message'] = strip_tags($value['message']);
                    } else {
                        $newArr[$i]['message'] = "No message for this" . $newArr[$i]['task_name'];
                    }
                    $i++;
                }
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $get_project_details = Project::find_by_id($project_id);
            if (empty($get_project_details)) {
                $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                $this->response($newdata);
            }

            //$company_id = $get_project_details->company_id;
            $allmilestonecomment = $this->db->query('SELECT * from  project_has_milestones_comment where project_id="' . $project_id . '" and company_id="' . $company_id . '"')->result_array();
            if (empty($allmilestonecomment)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Milestones comments yet'));
                $this->response($newdata);
            }

            $newArr = array();
            $i = 0;
            foreach ($allmilestonecomment as $key => $value) {
                $newArr[$i]['id'] = $value['id'];
                $newArr[$i]['project_name'] = $get_project_details->name;
                $newArr[$i]['user_id'] = $value['user_id'];
                $get_user = User::find($newArr[$i]['user_id']);
                $newArr[$i]['user'] = $get_user->firstname . " " . $get_user->lastname;
                $newArr[$i]['milestone_id'] = $value['milestone_id'];
                $get_milestone_name = ProjectHasMilestone::find_by_id($newArr[$i]['milestone_id']);
                $newArr[$i]['milestone_name'] = $get_milestone_name->name;
                if (!empty($value['message'])) {
                    $newArr[$i]['message'] = strip_tags($value['message']);
                } else {
                    $newArr[$i]['message'] = "No message for this" . $newArr[$i]['task_name'];
                }
                $i++;
            }
            if (empty($newArr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
            $this->response($newdata);
        }

        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* All Milestones By Project */

    function allmilestonesbyproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
          LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $company_id = $get_data['company_id'];
            if ($get_data['role_id'] == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $get_project_details = Project::find_by_id($project_id);
                if (empty($get_project_details)) {
                    $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                    $this->response($newdata);
                }

                $company_id = $get_project_details->company_id;
                $allmilestones = $this->db->query('SELECT * from project_has_milestones where project_id="' . $project_id . '"')->result_array();
                if (empty($allmilestones)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Milestones yet'));
                    $this->response($newdata);
                }

                $newArr = array();
                $i = 0;
                foreach ($allmilestones as $key => $value) {
                    $newArr[$i]['id'] = $value['id'];
                    $newArr[$i]['project_name'] = $get_project_details->name;
                    $newArr[$i]['name'] = $value['name'];
                    $newArr[$i]['due_date'] = $value['due_date'];
                    $newArr[$i]['start_date'] = $value['start_date'];
                    if (!empty($value['description'])) {
                        $newArr[$i]['description'] = strip_tags($value['description']);
                    } else {
                        $newArr[$i]['description'] = "";
                    }
                    $i++;
                }
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $get_project_details = Project::find_by_id($project_id);
            if (empty($get_project_details)) {
                $newdata = array('result' => 'error', 'response' => array('code' => 404, 'message' => 'project data not found'));
                $this->response($newdata);
            }

            $company_id = $get_project_details->company_id;
            $allmilestones = $this->db->query('SELECT * from project_has_milestones where project_id="' . $project_id . '"')->result_array();
            if (empty($allmilestones)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No Tasks comments yet'));
                $this->response($newdata);
            }

            $newArr = array();
            $i = 0;
            foreach ($allmilestones as $key => $value) {
                $newArr[$i]['id'] = $value['id'];
                $newArr[$i]['project_name'] = $get_project_details->name;
                $newArr[$i]['name'] = $value['name'];
                $newArr[$i]['due_date'] = $value['due_date'];
                $newArr[$i]['start_date'] = $value['start_date'];
                if (!empty($value['description'])) {
                    $newArr[$i]['description'] = strip_tags($value['description']);
                } else {
                    $newArr[$i]['description'] = "";
                }
                $i++;
            }
            if (empty($newArr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* Add Project */

    function addproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                        LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
        if (empty($get_data)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
            $this->response($newdata);
        }
        $role_id = $get_data['role_id'];
        if ($role_id == 2) {
            $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : '';
            $start = trim(htmlspecialchars($_REQUEST['start'])) ? trim(htmlspecialchars($_REQUEST['start'])) : '';
            $end = trim(htmlspecialchars($_REQUEST['end'])) ? trim(htmlspecialchars($_REQUEST['end'])) : '';

            if (empty($name)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter Project name'));
                $this->response($newdata);
            }
            if (empty($start)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter Start Date'));
                $this->response($newdata);
            }
            if (empty($end)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter End Date'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            $user_email = $get_data['email'];
            $get_compnay = Company::find_by_id($company_id);
            $reference = $this->settings->project_reference;
            $progress = trim(htmlspecialchars($_REQUEST['progress'])) ? trim(htmlspecialchars($_REQUEST['progress'])) : 0;
            $progress_calc = trim(htmlspecialchars($_REQUEST['progress_calc'])) ? trim(htmlspecialchars($_REQUEST['progress_calc'])) : 0;
            $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : '';
            $phases = trim(htmlspecialchars($_REQUEST['phases'])) ? trim(htmlspecialchars($_REQUEST['phases'])) : 'Planning,Developing,Testing';
            //else {
            $post_arr = array(
                'reference' => $reference,
                'progress' => $progress,
                'name' => $name,
                'progress_calc' => $progress_calc,
                'start' => $start,
                'end' => $end,
                'description' => $description,
                'company_id' => $company_id,
                'phases' => $phases,
                'datetime' => time()
            );
            $project = Project::create($post_arr);
            if (!$project) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Create Project Unsuccessfully'));
                $this->response($newdata);
            }
            //else {
            $new_project_reference = $reference + 1;
            $project_reference = Setting::first();
            $project_reference->update_attributes(array('project_reference' => $new_project_reference));
            $attributes = array('project_id' => $project->id, 'user_id' => $this->user->id);
            ProjectHasWorker::create($attributes);
            $project_assign_clients = trim(htmlspecialchars($_REQUEST['project_assign_clients'])) ? trim(htmlspecialchars($_REQUEST['project_assign_clients'])) : '';
            if (empty($project_assign_clients)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project->id, 'message' => 'Create Project Successfully'));
                $this->response($newdata);
            }
            $check_company_for_users = $this->db->query('SELECT DISTINCT (u.id), u.firstname, u.lastname, r.role_id FROM users u JOIN user_roles r ON u.id = r.user_id WHERE r.user_id IN ("'.$project_assign_clients.'") AND (r.role_id =3 OR r.role_id =4) AND r.company_id ="'.$company_id.'" AND u.status != "deleted"')->result_array();
            if (empty($check_company_for_users)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'project_id'=>$project->id, 'message' => 'Project Added Successfully But This user in different company So Can`t Assign This Project'));
                $this->response($newdata);
            }
            $new_assign_clients = explode(",", $project_assign_clients);
            //else {

            $delete_project_assign_user = "DELETE from project_assign_clients where project_id ='" . $project->id . "'";
            $this->db->query($delete_project_assign_user);
            $assign_arr = count($new_assign_clients);

            /* $config['protocol']    = 'smtp';
              $config['smtp_host']    = 'ssl://smtp.gmail.com';
              $config['smtp_port']    = '465';
              $config['smtp_timeout'] = '7';
              $config['smtp_user']    = 'emailtesterone@gmail.com';
              $config['smtp_pass']    = 'kgn@123456';
              $config['charset']    = 'utf-8';
              $config['newline']    = "\r\n";
              $config['mailtype'] = 'html';
              $config['validation'] = TRUE; // bool whether to result email or not

              $this->email->initialize($config); */

            $this->load->library('email');

            for ($i = 0; $i < $assign_arr; $i++) {
                $assign_id = $new_assign_clients[$i];
                $newArr = array('project_id' => $project->id, 'company_id' => $company_id, 'assign_user_id' => $assign_id);
                //echo "<pre>";print_r($newArr);
                $insert_data = ProjectAssignClients::create($newArr);

                $get_user_details = User::find_by_id($new_assign_clients[$i]);
                $get_user_role = $this->db->query('select * from user_roles where company_id="' . $company_id . '" and user_id="' . $new_assign_clients[$i] . '"')->row_array();
                $get_company_details = $this->db->query('select * from company_details where company_id="' . $company_id . '"')->row_array();
                if (!empty($get_company_details)) {
                    $c_logo = $get_company_details['logo'];
                    if (!empty($c_logo)) {
                        $company_logo = site_url() . $c_logo;
                    } else {
                        $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                    }
                } else {
                    $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                }

                if (!empty($get_user_role)) {
                    $role_id = $get_user_role['role_id'];
                    $project_link;
                    if ($role_id == 3) {
                        $project_link = base_url() . 'cprojects';
                    } elseif ($role_id == 4) {
                        $project_link = base_url() . 'scprojects';
                    } else {
                        $project_link = base_url() . 'aoprojects';
                    }
                }
                //echo "<pre>";print_r($get_user_details);

                $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                $this->email->to($get_user_details->email);
                $this->email->subject('Spera Project Assign');
                $send_message = "Hi " . trim($get_user_details->firstname . " " . $get_user_details->lastname) . "<br/> 
                                        Company_Name: " . $get_compnay->name . "<br/>
                                        Company_Logo: <img src='" . $company_logo . "' alt='image'/><br/>
                                        Project Name: " . $name . "<br/>
                                        Project Link: " . $project_link . "<br/>
                                        Project Description: " . $description . "<br/><br/><br/>
                                        Thanks<br/>
                                        Spera Team";
                //echo $send_message;exit; 
                $this->email->message($send_message);

                $mail_sent = null;
                if ($this->email->send()) {
                    $mail_sent = 'Project Assign mail sent.';
                }
            }
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project->id, 'message' => 'Create Project Successfully'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'You are not Business User So Can`t Create Project'));
        $this->response($newdata);
    }

    /* Get Project */

    function getproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                        LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }

            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $user_email = $get_data['email'];
                $get_compnay = Company::find_by_id($company_id);
                $project_details = Project::find_by_id($project_id);
                if (!$project_details) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'project id not found'));
                    $this->response($newdata);
                }

                if ($project_details->description != '') {
                    $description = strip_tags($project_details->description);
                } else {
                    $description = "";
                }
                $newArr = array(
                    'company_id' => $company_id,
                    'project_id' => $project_id,
                    'name' => $project_details->name,
                    'progress' => $project_details->progress,
                    'reference' => $project_details->reference,
                    'description' => $description,
                    'start' => $project_details->start,
                    'end' => $project_details->end,
                    'phases' => $project_details->phases
                );
                if ($project_details->tracking) {
                    $newArr['tracking'] = $project_details->tracking;
                } else {
                    $newArr['tracking'] = 0;
                }
                if (!empty($project_details->tracking)) {
                    $newArr['tracking'] = $project_details->tracking;
                } else {
                    $newArr['tracking'] = 0;
                }
                if (!empty($project_details->time_spent)) {
                    $newArr['time_spent'] = $project_details->time_spent;
                } else {
                    $newArr['time_spent'] = 0;
                }

                $p_assign_query = 'SELECT assign_user_id from project_assign_clients where project_id="' . $project_id . '"';
                $project_assign_clients = $this->db->query($p_assign_query)->result_array();

                if (!empty($project_assign_clients)) {
                    $project_assign_users = array_column($project_assign_clients, 'assign_user_id');
                    $newArr['project_assign_users'] = $project_assign_clients;
                } else {
                    $newArr['project_assign_users'] = "";
                }
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }
                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $user_email = $get_data['email'];
            $get_compnay = Company::find_by_id($company_id);
            $project_details = Project::find_by_id($project_id);
            if (!$project_details) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'project id not found'));
                $this->response($newdata);
            }

            if ($project_details->description != '') {
                $description = strip_tags($project_details->description);
            } else {
                $description = "";
            }
            $newArr = array(
                'company_id' => $company_id,
                'project_id' => $project_id,
                'name' => $project_details->name,
                'progress' => $project_details->progress,
                'reference' => $project_details->reference,
                'description' => $description,
                'start' => $project_details->start,
                'end' => $project_details->end,
                'phases' => $project_details->phases
            );
            if ($project_details->tracking) {
                $newArr['tracking'] = $project_details->tracking;
            } else {
                $newArr['tracking'] = 0;
            }
            if (!empty($project_details->tracking)) {
                $newArr['tracking'] = $project_details->tracking;
            } else {
                $newArr['tracking'] = 0;
            }
            if (!empty($project_details->time_spent)) {
                $newArr['time_spent'] = $project_details->time_spent;
            } else {
                $newArr['time_spent'] = 0;
            }

            $p_assign_query = 'SELECT assign_user_id from project_assign_clients where project_id="' . $project_id . '"';
            $project_assign_clients = $this->db->query($p_assign_query)->result_array();

            if (!empty($project_assign_clients)) {
                $project_assign_users = array_column($project_assign_clients, 'assign_user_id');
                $newArr['project_assign_users'] = $project_assign_clients;
            } else {
                $newArr['project_assign_users'] = "";
            }
            if (empty($newArr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }
            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
            $this->response($newdata);
        }

        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* Update Project */

    function updateproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        //else {
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        //else {
        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        //else {
        if (is_numeric($project_id)) {

            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                            LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }

            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $user_email = $get_data['email'];
                $get_compnay = Company::find_by_id($company_id);
                $p_assign_query = 'SELECT assign_user_id from project_assign_clients where project_id="' . $project_id . '"';
                $p_assign_clients = $this->db->query($p_assign_query)->result_array();
                //var_dump($p_assign_clients);exit; 
                if (!empty($p_assign_clients)) {
                    $project_assign_users = array_column($p_assign_clients, 'assign_user_id');
                } else {
                    $project_assign_users = array();
                }
                $s_projects_assign_clients = implode(',', $project_assign_users);
                $project = Project::find($project_id);
                $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : $project->name;
                $start = trim(htmlspecialchars($_REQUEST['start'])) ? trim(htmlspecialchars($_REQUEST['start'])) : $project->start;
                $end = trim(htmlspecialchars($_REQUEST['end'])) ? trim(htmlspecialchars($_REQUEST['end'])) : $project->end;
//                if (empty($name)) {
//                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Please enter Project name'));
//                    $this->response($newdata);
//                }
//                if (empty($start)) {
//                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Please enter Start Date'));
//                    $this->response($newdata);
//                }
//                if (empty($end)) {
//                    $newdata = array('result' => 'fail', 'response' => array('code'=>400,'message' => 'Please enter End Date'));
//                    $this->response($newdata);
//                }
                $reference = $project->reference;
                $progress = trim(htmlspecialchars($_REQUEST['progress'])) ? trim(htmlspecialchars($_REQUEST['progress'])) : $project->progress;
                $progress_calc = trim(htmlspecialchars($_REQUEST['progress_calc'])) ? trim(htmlspecialchars($_REQUEST['progress_calc'])) : $project->progress_calc;
                $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : $project->description;
                $phases = trim(htmlspecialchars($_REQUEST['phases'])) ? trim(htmlspecialchars($_REQUEST['phases'])) : $project->phases;
                //else {
                $post_arr = array(
                    'reference' => $reference,
                    'progress' => $progress,
                    'name' => $name,
                    'progress_calc' => $progress_calc,
                    'start' => $start,
                    'end' => $end,
                    'description' => $description,
                    'company_id' => $company_id,
                    'phases' => $phases,
                    'datetime' => time()
                );

                $project->update_attributes($post_arr);
                if (!$project) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Update Project Unsuccessfully'));
                    $this->response($newdata);
                }
                //else {
                $project_assign_clients = trim(htmlspecialchars($_REQUEST['project_assign_clients'])) ? trim(htmlspecialchars($_REQUEST['project_assign_clients'])) : $s_projects_assign_clients;
                if (empty($project_assign_clients)) {
                    $delete_project_assign_user = "DELETE from project_assign_clients where project_id ='" . $project->id . "'";
                    $this->db->query($delete_project_assign_user);
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Update Project Successfully'));
                    $this->response($newdata);
                }
                $check_company_for_users = $this->db->query('SELECT DISTINCT (u.id), u.firstname, u.lastname, r.role_id FROM users u JOIN user_roles r ON u.id = r.user_id WHERE r.user_id IN ("'.$project_assign_clients.'") AND (r.role_id =3 OR r.role_id =4) AND r.company_id ="'.$company_id.'" AND u.status != "deleted"')->result_array();
                if (empty($check_company_for_users)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Update Sucessfully But This user in different company so cant assingn this project'));
                    $this->response($newdata);
                }
                $new_assign_clients = explode(",", $project_assign_clients);
                
                //else {
                $delete_project_assign_user = "DELETE from project_assign_clients where project_id ='" . $project->id . "'";
                $this->db->query($delete_project_assign_user);
                $assign_arr = count($new_assign_clients);
                for ($i = 0; $i < $assign_arr; $i++) {
                    $assign_id = $new_assign_clients[$i];
                    $newArr = array('project_id' => $project->id, 'company_id' => $company_id, 'assign_user_id' => $assign_id);
                    //echo "<pre>";print_r($newArr);
                    $insert_data = ProjectAssignClients::create($newArr);
                }

                $this->load->library('email');

                $assigned_user = array_diff($new_assign_clients, $project_assign_users);

                if ($assigned_user[0] != '') {
                    $count_assigned_user = count($assigned_user);
                    for ($j = 0; $j < $count_assigned_user; $j++) {
                        $get_user_details = User::find_by_id($assigned_user[$j]);
                        $get_user_role = $this->db->query('select * from user_roles where company_id="' . $company_id . '" and user_id="' . $assigned_user[$j] . '"')->row_array();
                        $get_company_details = $this->db->query('select * from company_details where company_id="' . $company_id . '"')->row_array();
                        if (!empty($get_company_details)) {
                            $c_logo = $get_company_details['logo'];
                            if (!empty($c_logo)) {
                                $company_logo = site_url() . $c_logo;
                            } else {
                                $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                            }
                        } else {
                            $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                        }

                        if (!empty($get_user_role)) {
                            $role_id = $get_user_role['role_id'];
                            $project_link;
                            if ($role_id == 3) {
                                $project_link = base_url() . 'cprojects';
                            } elseif ($role_id == 4) {
                                $project_link = base_url() . 'scprojects';
                            } else {
                                $project_link = base_url() . 'aoprojects';
                            }
                        }

                        $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                        $this->email->to($get_user_details->email);
                        $this->email->subject('Spera Project Assign');
                        $send_message = "Hi " . trim($get_user_details->firstname . " " . $get_user_details->lastname) . "<br/> 
                                                    Company_Name: " . $get_compnay->name . "<br/>
                                                    Company_Logo: <img src='" . $company_logo . "' alt='image'/><br/>
                                                    Project Name: " . $name . "<br/>
                                                    Project Link: " . $project_link . "<br/>
                                                    Project Description: " . $description . "<br/><br/><br/>
                                                    Thanks<br/>
                                                    Spera Team";
                        $this->email->message($send_message);

                        $mail_sent = null;
                        if ($this->email->send()) {
                            $mail_sent = 'Project Assign mail sent.';
                        }
                    }
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Update Project Successfully'));
                    $this->response($newdata);
                }
                //else {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Update Project Successfully'));
                $this->response($newdata);
            }
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'You are not Business User So Can`t Update Project'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* Delete Project */

    function deleteproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';

        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                            LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $company_id = $get_data['company_id'];
            $role_id = $get_data['role_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $sql = 'DELETE FROM project_has_tasks WHERE project_id = "' . $project_id . '"';
                $this->db->query($sql);

                $delete_task_sql = 'DELETE FROM project_assign_tasks WHERE project_id = "' . $project_id . '"';
                $this->db->query($delete_task_sql);

                $delete_assign_clients_sql = 'DELETE FROM project_assign_clients WHERE project_id = "' . $project_id . '"';
                $this->db->query($delete_assign_clients_sql);

                $delete_project_activities_sql = 'DELETE FROM project_has_activities WHERE project_id = "' . $project_id . '"';
                $this->db->query($delete_project_activities_sql);

                $delete_project_files_sql = 'DELETE FROM project_has_files WHERE project_id = "' . $project_id . '"';
                $this->db->query($delete_project_files_sql);

                $milestone_delete_attachment = $this->db->query("SELECT * from project_has_milestones_attachment where project_id='" . $project_id . "'")->result_array();
                if (!empty($milestone_delete_attachment)) {
                    foreach ($milestone_delete_attachment as $kk => $vv) {
                        $path = FCPATH . 'files/milestone_attachment/' . $vv['milestone_attach_file'];
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                    $delete_mile_attachment = $this->db->query('DELETE From project_has_milestones_attachment where project_id="' . $project_id . '"');
                }

                $delete_project_milestones_sql = 'DELETE FROM project_has_milestones WHERE project_id = "' . $project_id . '"';
                $this->db->query($delete_project_milestones_sql);

                $tasks_delete_attachment = $this->db->query("SELECT * from project_has_tasks_attachment where project_id='" . $project_id . "'")->result_array();
                if (!empty($tasks_delete_attachment)) {
                    foreach ($tasks_delete_attachment as $kk => $vv) {
                        $path = FCPATH . 'files/tasks_attachment/' . $vv['task_attach_file'];
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                    $delete_task_attachment = $this->db->query('DELETE From project_has_tasks_attachment where project_id="' . $project_id . '"');
                }

                $delete_project_timesheets_sql = 'DELETE FROM  project_has_timesheets WHERE project_id = "' . $project_id . '"';
                $this->db->query($delete_project_timesheets_sql);

                $delete_project_workers_sql = 'DELETE FROM  project_has_workers WHERE project_id = "' . $project_id . '"';
                $this->db->query($delete_project_workers_sql);

                $this->db->query('Delete from projects where id="' . $project_id . '"');
                if ($this->db->_error_message()) {
                    $result = 'Error! [' . $this->db->_error_message() . ']';
                } else if (!$this->db->affected_rows()) {
                    $result = 'Error! ID [' . $project_id . '] not found';
                } else {
                    $result = 'Success';
                }
                if ($result == 'Success') {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Delete project Successfully'));
                    $this->response($newdata);
                }
                $newdata = array('result' => 'fail', 'response' => array('message' => $result, 'code' => 400));
                $this->response($newdata);
            }
            $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'You are not Business User So Can`t Delete Project'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* Add Task */

    function addtask() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }


        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                        LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            //else {
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $public = 1;
                $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : '';
                $priority = trim(htmlspecialchars($_REQUEST['priority'])) ? trim(htmlspecialchars($_REQUEST['priority'])) : 2;
                $status = trim(htmlspecialchars($_REQUEST['status'])) ? trim(htmlspecialchars($_REQUEST['status'])) : 'open';
                $value = trim(htmlspecialchars($_REQUEST['value'])) ? trim(htmlspecialchars($_REQUEST['value'])) : 0;
                $due_date = trim(htmlspecialchars($_REQUEST['due_date'])) ? trim(htmlspecialchars($_REQUEST['due_date'])) : '';
                $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : '';

                if (empty($name)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter task name'));
                    $this->response($newdata);
                }
                //else {
                $post_arr = array(
                    'project_id' => $project_id,
                    'public' => $public,
                    'user_id' => $this->user_id,
                    'name' => $name,
                    'priority' => $priority,
                    'status' => $status,
                    'value' => $value,
                    'due_date' => $due_date,
                    'description' => $description
                );
//                                $assign_client_id = trim($_REQUEST['assign_client_id']) ? trim($_REQUEST['assign_client_id']) : '';
//                                $new_assign_clients = explode(",", $assign_client_id);
                //echo "<pre>";print_r($new_assign_clients);die;
                $task = ProjectHasTask::create($post_arr);
                if (!$task) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Tasks Added Unsuccessfully'));
                    $this->response($newdata);
                }
                //else {
                $assign_client_id = trim($_REQUEST['assign_client_id']) ? trim($_REQUEST['assign_client_id']) : '';
                if (empty($assign_client_id)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project_id, 'task_id' => $task->id, 'message' => 'Tasks Added Successfully'));
                    $this->response($newdata);
                }
                $check_users_for_projects = $this->db->query('SELECT * FROM project_assign_clients WHERE assign_user_id IN ("' . $assign_client_id . '") AND company_id ="' . $company_id . '" and project_id="'.$project_id.'"')->result_array();
                if (empty($check_users_for_projects)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400,'task_id'=>$task->id,'message' => 'Task Added Successfully But This user not Assign this project so cant assign this task'));
                    $this->response($newdata);
                }
                $new_assign_clients = explode(",", $assign_client_id);
                //echo "<pre>";print_r($new_assign_clients);die;
                
                //else {

                $user_email = $get_data['email'];
                $get_compnay = Company::find_by_id($company_id);
                $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task->id . "'";
                $this->db->query($delete_task_assign_user);
                $assign_arr = count($new_assign_clients);

                /* $config_email['protocol']    = 'smtp';
                  $config_email['smtp_host']    = 'ssl://smtp.gmail.com';
                  $config_email['smtp_port']    = '465';
                  $config_email['smtp_timeout'] = '7';
                  $config_email['smtp_user']    = 'emailtesterone@gmail.com';
                  $config_email['smtp_pass']    = 'kgn@123456';
                  $config_email['charset']    = 'utf-8';
                  $config_email['newline']    = "\r\n";
                  $config_email['mailtype'] = 'html';
                  $config_email['validation'] = TRUE; // bool whether to result email or not

                  $this->email->initialize($config_email); */
                for ($i = 0; $i < $assign_arr; $i++) {
                    $assign_id = $new_assign_clients[$i];
                    $newArr = array('task_id' => $task->id, 'project_id' => $task->project_id, 'assign_user_id' => $assign_id);
                    $insert_data = ProjectAssignTasks::create($newArr);
                    $get_user_details = User::find_by_id($new_assign_clients[$i]);
                    $project_details = Project::find_by_id($project_id);
                    $get_user_role = $this->db->query('select * from user_roles where company_id="' . $company_id . '" and user_id="' . $new_assign_clients[$i] . '"')->row_array();
                    $get_company_details = $this->db->query('select * from company_details where company_id="' . $company_id . '"')->row_array();
                    if (!empty($get_company_details)) {
                        $c_logo = $get_company_details['logo'];
                        if (!empty($c_logo)) {
                            $company_logo = site_url() . $c_logo;
                        } else {
                            $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                        }
                    } else {
                        $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                    }

                    if (!empty($get_user_role)) {
                        $role_id = $get_user_role['role_id'];
                        $project_link;
                        if ($role_id == 3) {
                            $project_link = base_url() . 'cprojects/view/' . $id;
                        } elseif ($role_id == 4) {
                            $project_link = base_url() . 'scprojects/view/' . $id;
                        } else {
                            $project_link = base_url() . 'aoprojects/view/' . $id;
                        }
                    }
                    //echo "<pre>";print_r($get_user_details->email);
                    $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                    $this->email->to($get_user_details->email);
                    $this->email->subject('Spera ' . $name . ' Assign for ' . $project_details->name . '');

                    $send_message = "Hi " . trim($get_user_details->firstname . " " . $get_user_details->lastname) . "<br/>
                                                <p>Company_Name: " . $get_compnay->name . "</p><br/>
                                                <p>Company_Logo: <img src='" . $company_logo . "' alt='image'/></p><br/>
                                                <p>Task Link: " . $project_link . "</p><br/>
                                                <p>Project Name: " . $project_details->name . "</p><br/>
                                                <p>Project Description: " . $project_details->description . "</p><br/>
                                                <p>Task Name: " . $name . "</p><br/>
                                                <p>Task Description: " . $description . "</p><br/><br/><br/>
                                                Thanks<br/>
                                                Spera Team";
                    $this->email->message($send_message);
                    $mail_sent = null;
                    if ($this->email->send()) {
                        $mail_sent = 'Task Assign mail sent.';
                    }
                }
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project_id, 'task_id' => $task->id, 'message' => 'Tasks Added Successfully'));
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $public = 1;
            $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : '';
            $priority = trim(htmlspecialchars($_REQUEST['priority'])) ? trim(htmlspecialchars($_REQUEST['priority'])) : 2;
            $status = trim(htmlspecialchars($_REQUEST['status'])) ? trim(htmlspecialchars($_REQUEST['status'])) : 'open';
            $value = trim(htmlspecialchars($_REQUEST['value'])) ? trim(htmlspecialchars($_REQUEST['value'])) : 0;
            $due_date = trim(htmlspecialchars($_REQUEST['due_date'])) ? trim(htmlspecialchars($_REQUEST['due_date'])) : '';
            $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : '';

            if (empty($name)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter task name'));
                $this->response($newdata);
            }
            //else {
            $post_arr = array(
                'project_id' => $project_id,
                'public' => $public,
                'user_id' => $this->user_id,
                'name' => $name,
                'priority' => $priority,
                'status' => $status,
                'value' => $value,
                'due_date' => $due_date,
                'description' => $description
            );
//                                $assign_client_id = trim($_REQUEST['assign_client_id']) ? trim($_REQUEST['assign_client_id']) : '';
//                                $new_assign_clients = explode(",", $assign_client_id);
            //echo "<pre>";print_r($new_assign_clients);die;
            $task = ProjectHasTask::create($post_arr);
            if (!$task) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Tasks Added Unsuccessfully'));
                $this->response($newdata);
            }
            //else {
            $assign_client_id = trim($_REQUEST['assign_client_id']) ? trim($_REQUEST['assign_client_id']) : '';
            if (empty($assign_client_id)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project_id, 'task_id' => $task->id, 'message' => 'Tasks Added Successfully'));
                    $this->response($newdata);
                }
            $check_users_for_projects = $this->db->query('SELECT * FROM project_assign_clients WHERE assign_user_id IN ("' . $assign_client_id . '") AND company_id ="' . $company_id . '" and project_id="'.$project_id.'"')->result_array();
            if (empty($check_users_for_projects)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400,'task_id'=>$task->id, 'message' => 'Task Added Successfully But This user not Assign this project so cant assign this task'));
                $this->response($newdata);
            }
            $new_assign_clients = explode(",", $assign_client_id);
            //echo "<pre>";print_r($new_assign_clients);die;
//            if (empty($new_assign_clients)) {
//                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project_id, 'task_id' => $task->id, 'message' => 'Tasks Added Successfully'));
//                $this->response($newdata);
//            }
            //else {

            $user_email = $get_data['email'];
            $get_compnay = Company::find_by_id($company_id);
            $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task->id . "'";
            $this->db->query($delete_task_assign_user);
            $assign_arr = count($new_assign_clients);

            /* $config_email['protocol']    = 'smtp';
              $config_email['smtp_host']    = 'ssl://smtp.gmail.com';
              $config_email['smtp_port']    = '465';
              $config_email['smtp_timeout'] = '7';
              $config_email['smtp_user']    = 'emailtesterone@gmail.com';
              $config_email['smtp_pass']    = 'kgn@123456';
              $config_email['charset']    = 'utf-8';
              $config_email['newline']    = "\r\n";
              $config_email['mailtype'] = 'html';
              $config_email['validation'] = TRUE; // bool whether to result email or not

              $this->email->initialize($config_email); */
            for ($i = 0; $i < $assign_arr; $i++) {
                $assign_id = $new_assign_clients[$i];
                $newArr = array('task_id' => $task->id, 'project_id' => $task->project_id, 'assign_user_id' => $assign_id);
                $insert_data = ProjectAssignTasks::create($newArr);
                $get_user_details = User::find_by_id($new_assign_clients[$i]);
                $project_details = Project::find_by_id($project_id);
                $get_user_role = $this->db->query('select * from user_roles where company_id="' . $company_id . '" and user_id="' . $new_assign_clients[$i] . '"')->row_array();
                $get_company_details = $this->db->query('select * from company_details where company_id="' . $company_id . '"')->row_array();
                if (!empty($get_company_details)) {
                    $c_logo = $get_company_details['logo'];
                    if (!empty($c_logo)) {
                        $company_logo = site_url() . $c_logo;
                    } else {
                        $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                    }
                } else {
                    $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                }

                if (!empty($get_user_role)) {
                    $role_id = $get_user_role['role_id'];
                    $project_link;
                    if ($role_id == 3) {
                        $project_link = base_url() . 'cprojects/view/' . $id;
                    } elseif ($role_id == 4) {
                        $project_link = base_url() . 'scprojects/view/' . $id;
                    } else {
                        $project_link = base_url() . 'aoprojects/view/' . $id;
                    }
                }
                //echo "<pre>";print_r($get_user_details->email);
                $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                $this->email->to($get_user_details->email);
                $this->email->subject('Spera ' . $name . ' Assign for ' . $project_details->name . '');

                $send_message = "Hi " . trim($get_user_details->firstname . " " . $get_user_details->lastname) . "<br/>
                                                <p>Company_Name: " . $get_compnay->name . "</p><br/>
                                                <p>Company_Logo: <img src='" . $company_logo . "' alt='image'/></p><br/>
                                                <p>Task Link: " . $project_link . "</p><br/>
                                                <p>Project Name: " . $project_details->name . "</p><br/>
                                                <p>Project Description: " . $project_details->description . "</p><br/>
                                                <p>Task Name: " . $name . "</p><br/>
                                                <p>Task Description: " . $description . "</p><br/><br/><br/>
                                                Thanks<br/>
                                                Spera Team";
                $this->email->message($send_message);
                $mail_sent = null;
                if ($this->email->send()) {
                    $mail_sent = 'Task Assign mail sent.';
                }
            }
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project_id, 'task_id' => $task->id, 'message' => 'Tasks Added Successfully'));
            $this->response($newdata);
        }

        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* Get Task */

    function gettask() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $task_id = trim(htmlspecialchars($_REQUEST['task_id'])) ? trim(htmlspecialchars($_REQUEST['task_id'])) : '';
        if (empty($project_id) || empty($task_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id) && is_numeric($task_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r
                        LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }

            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $check_task_for_project_or_not = $this->db->query('SELECT count(*) as count From project_has_tasks where project_id="' . $project_id . '" and id="' . $task_id . '"')->row_array();
                if ($check_task_for_project_or_not['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task not for This Project'));
                    $this->response($newdata);
                }

                $check_project_task_assign_user = $this->db->query("SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.project_id = '" . $project_id . "' AND r.company_id = '" . $company_id . "' AND r.role_id ='" . $role_id . "' and r.user_id='" . $this->user_id . "' and t.id='" . $task_id . "'UNION
            SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
            join user_roles r on at.assign_user_id=r.user_id where r.company_id='" . $company_id . "' and r.role_id='" . $role_id . "' and r.user_id='" . $this->user_id . "' AND t.project_id = '" . $project_id . "' and t.id='" . $task_id . "'
            ")->row_array();
                //echo "<pre>";print_r($check_project_task_assign_user);exit;
                if (empty($check_project_task_assign_user)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task Not Assign to This project to you'));
                    $this->response($newdata);
                }
                $get_compnay = Company::find_by_id($company_id);
                $task_details = ProjectHasTask::find_by_id($task_id);
                if (!$task_details) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Task id not found'));
                    $this->response($newdata);
                }

                if ($task_details->description != '') {
                    $description = strip_tags($task_details->description);
                } else {
                    $description = "";
                }
                $newArr = array(
                    'company_id' => $company_id,
                    'public' => $task_details->public,
                    'project_id' => $project_id,
                    'name' => $task_details->name,
                    'priority' => $task_details->priority,
                    'description' => $description,
                    'due_date' => $task_details->due_date,
                    'value' => $task_details->value,
                    'status' => $task_details->status
                );

                $t_assign_query = 'SELECT assign_user_id from project_assign_tasks where task_id="' . $task_id . '"';
                $task_assign_users = $this->db->query($t_assign_query)->result_array();
                if (!empty($task_assign_users)) {
                    $task_assign_clients = array_column($task_assign_users, 'assign_user_id');
                    $newArr['task_assign_users'] = $task_assign_users;
                } else {
                    $newArr['task_assign_users'] = "";
                }
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }

            $check_task_for_project_or_not = $this->db->query('SELECT count(*) as count From project_has_tasks where project_id="' . $project_id . '" and id="' . $task_id . '"')->row_array();
            if ($check_task_for_project_or_not['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task not for This Project'));
                $this->response($newdata);
            }

            $get_compnay = Company::find_by_id($company_id);
            $task_details = ProjectHasTask::find_by_id($task_id);
            if (!$task_details) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'Task id not found'));
                $this->response($newdata);
            }

            if ($task_details->description != '') {
                $description = strip_tags($task_details->description);
            } else {
                $description = "";
            }
            $newArr = array(
                'company_id' => $company_id,
                'public' => $task_details->public,
                'project_id' => $project_id,
                'name' => $task_details->name,
                'priority' => $task_details->priority,
                'description' => $description,
                'due_date' => $task_details->due_date,
                'value' => $task_details->value,
                'status' => $task_details->status
            );

            $t_assign_query = 'SELECT assign_user_id from project_assign_tasks where task_id="' . $task_id . '"';
            $task_assign_users = $this->db->query($t_assign_query)->result_array();
            if (!empty($task_assign_users)) {
                $task_assign_clients = array_column($task_assign_users, 'assign_user_id');
                $newArr['task_assign_users'] = $task_assign_users;
            } else {
                $newArr['task_assign_users'] = "";
            }
            if (empty($newArr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id or task id is not numeric'));
        $this->response($newdata);
    }

    /* Update Task */

    function updatetask() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $task_id = trim(htmlspecialchars($_REQUEST['task_id'])) ? trim(htmlspecialchars($_REQUEST['task_id'])) : '';
        if (empty($project_id) || empty($task_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id) && is_numeric($task_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $check_task_for_project_or_not = $this->db->query('SELECT count(*) as count From project_has_tasks where project_id="' . $project_id . '" and id="' . $task_id . '"')->row_array();
                if ($check_task_for_project_or_not['count'] != 1) {
                    $newdata = array('result' => 'error', 'response' => array('code' => 400, 'message' => 'This Task not for This Project'));
                    $this->response($newdata);
                }

                $check_project_task_assign_user = $this->db->query("SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.project_id = '" . $project_id . "' AND r.company_id = '" . $company_id . "' AND r.role_id ='" . $role_id . "' and r.user_id='" . $this->user_id . "' and t.id='" . $task_id . "'UNION
            SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
            join user_roles r on at.assign_user_id=r.user_id where r.company_id='" . $company_id . "' and r.role_id='" . $role_id . "' and r.user_id='" . $this->user_id . "' AND t.project_id = '" . $project_id . "' and t.id='" . $task_id . "'
            ")->row_array();
                //echo "<pre>";print_r($check_project_task_assign_user);exit;
                if (empty($check_project_task_assign_user)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task Not Assign to This project to you'));
                    $this->response($newdata);
                }
                $task = ProjectHasTask::find_by_id($task_id);
                $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : $task->name;
                if (empty($name)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter task name'));
                    $this->response($newdata);
                }
                $public = $task->public;
                $priority = trim(htmlspecialchars($_REQUEST['priority'])) ? trim(htmlspecialchars($_REQUEST['priority'])) : $task->priority;
                $status = trim(htmlspecialchars($_REQUEST['status'])) ? trim(htmlspecialchars($_REQUEST['status'])) : $task->status;
                $value = trim(htmlspecialchars($_REQUEST['value'])) ? trim(htmlspecialchars($_REQUEST['value'])) : $task->value;
                $due_date = trim(htmlspecialchars($_REQUEST['due_date'])) ? trim(htmlspecialchars($_REQUEST['due_date'])) : $task->due_date;
                $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : $task->description;
                $t_assign_query = 'SELECT assign_user_id from project_assign_tasks where task_id="' . $task_id . '"';
                $task_assign_users = $this->db->query($t_assign_query)->result_array();
                if (!empty($task_assign_users)) {
                    $task_assign_clients = array_column($task_assign_users, 'assign_user_id');
                } else {
                    $task_assign_clients = array();
                }
                $s_projects_users=implode(',',$task_assign_clients);
                $post_arr = array(
                    'public' => $public,
                    'user_id' => $this->user_id,
                    'name' => $name,
                    'priority' => $priority,
                    'status' => $status,
                    'value' => $value,
                    'due_date' => $due_date,
                    'description' => $description,
                    'project_id' => $project_id
                );

                if ($task->user_id != $this->user_id) {
                    //stop timer and add time to timesheet
                    if ($task->tracking != 0) {
                        $now = time();
                        $diff = $now - $task->tracking;
                        $timer_start = $task->tracking;
                        $task->time_spent = $task->time_spent + $diff;
                        $task->tracking = "";
                        $attributes = array(
                            'task_id' => $task->id,
                            'user_id' => $task->user_id,
                            'project_id' => $task->project_id,
                            'client_id' => 0,
                            'time' => $diff,
                            'start' => $timer_start,
                            'end' => $now
                        );
                        $timesheet = ProjectHasTimesheet::create($attributes);
                    }
                }
                $task->update_attributes($post_arr);
                if (!$task) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Task Updated Unsuccessfully'));
                    $this->response($newdata);
                }
                //else {
                $assign_client_id = trim(htmlspecialchars($_REQUEST['assign_client_id'])) ? trim(htmlspecialchars($_REQUEST['assign_client_id'])) : $s_projects_users;
                if (empty($assign_client_id)) {
                    $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
                    $this->db->query($delete_task_assign_user);
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Updated Successfully'));
                    $this->response($newdata);
                }
                $check_users_for_projects = $this->db->query('SELECT * FROM project_assign_clients WHERE assign_user_id IN ("' . $assign_client_id . '") AND company_id ="' . $company_id . '" and project_id="'.$project_id.'"')->result_array();
                if (empty($check_users_for_projects)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Task Updated Successfully But This user not Assign this project so cant assign this task'));
                    $this->response($newdata);
                }
                $new_assign_clients = explode(",", $assign_client_id);
                //echo "<pre>";print_r($new_assign_clients);exit;
//                if (empty($new_assign_clients)) {
//                    $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
//                    $this->db->query($delete_task_assign_user);
//                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Updated Successfully'));
//                    $this->response($newdata);
//                }
                //else {
                //else {

                $user_email = $get_data['email'];
                $get_compnay = Company::find_by_id($company_id);
                $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
                $this->db->query($delete_task_assign_user);
                $update_assign_arr = count($new_assign_clients);
                for ($j = 0; $j < $update_assign_arr; $j++) {
                    $update_assign_id = $new_assign_clients[$j];
                    $update_newArr = array('task_id' => $task->id, 'project_id' => $task->project_id, 'assign_user_id' => $update_assign_id);
                    $update_data = ProjectAssignTasks::create($update_newArr);
                }

                /* $config_email['protocol']    = 'smtp';
                  $config_email['smtp_host']    = 'ssl://smtp.gmail.com';
                  $config_email['smtp_port']    = '465';
                  $config_email['smtp_timeout'] = '7';
                  $config_email['smtp_user']    = 'emailtesterone@gmail.com';
                  $config_email['smtp_pass']    = 'kgn@123456';
                  $config_email['charset']    = 'utf-8';
                  $config_email['newline']    = "\r\n";
                  $config_email['mailtype'] = 'html';
                  $config_email['validation'] = TRUE; // bool whether to result email or not

                  $this->email->initialize($config_email); */

                $this->load->library('email');

                $task_assigned_users = array_diff($new_assign_clients, $task_assign_clients);
                //var_dump($task_assigned_users);exit;
                if ($task_assigned_users[0] != '') {
                    $count_task_assign_users = count($task_assigned_users);
                    //exit;
                    for ($mn = 0; $mn < $count_task_assign_users; $mn++) {
                        $get_user_details = User::find_by_id($task_assigned_users[$mn]);

                        $project_details = Project::find_by_id($project_id);

                        $get_user_role = $this->db->query('select * from user_roles where company_id="' . $company_id . '" and user_id="' . $task_assigned_users[$mn] . '"')->row_array();

                        $get_company_details = $this->db->query('select * from company_details where company_id="' . $company_id . '"')->row_array();

                        if (!empty($get_company_details)) {
                            $c_logo = $get_company_details['logo'];
                            if (!empty($c_logo)) {
                                $company_logo = site_url() . $c_logo;
                            } else {
                                $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                            }
                        } else {
                            $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                        }

                        if (!empty($get_user_role)) {
                            $role_id = $get_user_role['role_id'];
                            $project_link;
                            if ($role_id == 3) {
                                $project_link = base_url() . 'cprojects/view/' . $id;
                            } elseif ($role_id == 4) {
                                $project_link = base_url() . 'scprojects/view/' . $id;
                            } else {
                                $project_link = base_url() . 'aoprojects/view/' . $id;
                            }
                        }
                        //echo "<pre>";print_r($get_user_details->email);
                        $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                        $this->email->to($get_user_details->email);
                        $this->email->subject('Spera ' . $name . ' Assign for ' . $project_details->name . '');

                        $send_message = "Hi " . trim($get_user_details->firstname . " " . $get_user_details->lastname) . "<br/>
                                                    <p>Company_Name: " . $get_compnay->name . "</p><br/>
                                                    <p>Company_Logo: <img src='" . $company_logo . "' alt='image'/></p><br/>
                                                    <p>Task Link: " . $project_link . "</p><br/>
                                                    <p>Project Name: " . $project_details->name . "</p><br/>
                                                    <p>Project Description: " . $project_details->description . "</p><br/>
                                                    <p>Task Name: " . $name . "</p><br/>
                                                    <p>Task Description: " . $description . "</p><br/><br/><br/>
                                                    Thanks<br/>
                                                    Spera Team";
                        $this->email->message($send_message);
                        $mail_sent = null;
                        if ($this->email->send()) {
                            $mail_sent = 'Task Assign mail sent.';
                        }
                    }
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Updated Successfully'));
                    $this->response($newdata);
                }
                //else {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Updated Successfully'));
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }

            $check_project_task_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_tasks WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and task_id="' . $task_id . '"')->row_array();
            if ($check_project_task_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task Not Assign to You'));
                $this->response($newdata);
            }
            $check_task_for_project_or_not = $this->db->query('SELECT count(*) as count From project_has_tasks where project_id="' . $project_id . '" and id="' . $task_id . '"')->row_array();
            if ($check_task_for_project_or_not['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task not for This Project'));
                $this->response($newdata);
            }
            $task = ProjectHasTask::find_by_id($task_id);
            $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : $task->name;
            if (empty($name)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter task name'));
                $this->response($newdata);
            }
            $public = $task->public;
            $priority = trim(htmlspecialchars($_REQUEST['priority'])) ? trim(htmlspecialchars($_REQUEST['priority'])) : $task->priority;
            $status = trim(htmlspecialchars($_REQUEST['status'])) ? trim(htmlspecialchars($_REQUEST['status'])) : $task->status;
            $value = trim(htmlspecialchars($_REQUEST['value'])) ? trim(htmlspecialchars($_REQUEST['value'])) : $task->value;
            $due_date = trim(htmlspecialchars($_REQUEST['due_date'])) ? trim(htmlspecialchars($_REQUEST['due_date'])) : $task->due_date;
            $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : $task->description;
            $t_assign_query = 'SELECT assign_user_id from project_assign_tasks where task_id="' . $task_id . '"';
            $task_assign_users = $this->db->query($t_assign_query)->result_array();
            if (!empty($task_assign_users)) {
                $task_assign_clients = array_column($task_assign_users, 'assign_user_id');
            } else {
                $task_assign_clients = array();
            }
            
            $s_projects_users=implode(',',$task_assign_clients);
            $post_arr = array(
                'public' => $public,
                'user_id' => $this->user_id,
                'name' => $name,
                'priority' => $priority,
                'status' => $status,
                'value' => $value,
                'due_date' => $due_date,
                'description' => $description,
                'project_id' => $project_id
            );

            if ($task->user_id != $this->user_id) {
                //stop timer and add time to timesheet
                if ($task->tracking != 0) {
                    $now = time();
                    $diff = $now - $task->tracking;
                    $timer_start = $task->tracking;
                    $task->time_spent = $task->time_spent + $diff;
                    $task->tracking = "";
                    $attributes = array(
                        'task_id' => $task->id,
                        'user_id' => $task->user_id,
                        'project_id' => $task->project_id,
                        'client_id' => 0,
                        'time' => $diff,
                        'start' => $timer_start,
                        'end' => $now
                    );
                    $timesheet = ProjectHasTimesheet::create($attributes);
                }
            }
            $task->update_attributes($post_arr);
            if (!$task) {
                $newdata = array('result' => 'error', 'response' => array('code' => 400, 'message' => 'Task Updated Unsuccessfully'));
                $this->response($newdata);
            }
            //else {
            $assign_client_id = trim(htmlspecialchars($_REQUEST['assign_client_id'])) ? trim(htmlspecialchars($_REQUEST['assign_client_id'])) : $s_projects_users;
            if (empty($assign_client_id)) {
                    $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
                    $this->db->query($delete_task_assign_user);
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Updated Successfully'));
                    $this->response($newdata);
                }
            $check_users_for_projects = $this->db->query('SELECT * FROM project_assign_clients WHERE assign_user_id IN ("' . $assign_client_id . '") AND company_id ="' . $company_id . '" and project_id="'.$project_id.'"')->result_array();
                if (empty($check_users_for_projects)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Task Updated Successfully But This user not Assign this project so cant assign this task'));
                    $this->response($newdata);
                }
            $new_assign_clients = explode(",", $assign_client_id);
            //echo "<pre>";print_r($new_assign_clients);exit;
//            if (empty($new_assign_clients)) {
//                $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
//                $this->db->query($delete_task_assign_user);
//                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Updated Successfully'));
//                $this->response($newdata);
//            }
            //else {
            //else {

            $user_email = $get_data['email'];
            $get_compnay = Company::find_by_id($company_id);
            $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
            $this->db->query($delete_task_assign_user);
            $update_assign_arr = count($new_assign_clients);
            for ($j = 0; $j < $update_assign_arr; $j++) {
                $update_assign_id = $new_assign_clients[$j];
                $update_newArr = array('task_id' => $task->id, 'project_id' => $task->project_id, 'assign_user_id' => $update_assign_id);
                $update_data = ProjectAssignTasks::create($update_newArr);
            }

            /* $config_email['protocol']    = 'smtp';
              $config_email['smtp_host']    = 'ssl://smtp.gmail.com';
              $config_email['smtp_port']    = '465';
              $config_email['smtp_timeout'] = '7';
              $config_email['smtp_user']    = 'emailtesterone@gmail.com';
              $config_email['smtp_pass']    = 'kgn@123456';
              $config_email['charset']    = 'utf-8';
              $config_email['newline']    = "\r\n";
              $config_email['mailtype'] = 'html';
              $config_email['validation'] = TRUE; // bool whether to result email or not

              $this->email->initialize($config_email); */

            $this->load->library('email');

            $task_assigned_users = array_diff($new_assign_clients, $task_assign_clients);
            //var_dump($task_assigned_users);exit;
            if ($task_assigned_users[0] != '') {
                $count_task_assign_users = count($task_assigned_users);
                //exit;
                for ($mn = 0; $mn < $count_task_assign_users; $mn++) {
                    $get_user_details = User::find_by_id($task_assigned_users[$mn]);

                    $project_details = Project::find_by_id($project_id);

                    $get_user_role = $this->db->query('select * from user_roles where company_id="' . $company_id . '" and user_id="' . $task_assigned_users[$mn] . '"')->row_array();

                    $get_company_details = $this->db->query('select * from company_details where company_id="' . $company_id . '"')->row_array();

                    if (!empty($get_company_details)) {
                        $c_logo = $get_company_details['logo'];
                        if (!empty($c_logo)) {
                            $company_logo = site_url() . $c_logo;
                        } else {
                            $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                        }
                    } else {
                        $company_logo = site_url() . 'files/media/FC2_logo_dark.png';
                    }

                    if (!empty($get_user_role)) {
                        $role_id = $get_user_role['role_id'];
                        $project_link;
                        if ($role_id == 3) {
                            $project_link = base_url() . 'cprojects/view/' . $id;
                        } elseif ($role_id == 4) {
                            $project_link = base_url() . 'scprojects/view/' . $id;
                        } else {
                            $project_link = base_url() . 'aoprojects/view/' . $id;
                        }
                    }
                    //echo "<pre>";print_r($get_user_details->email);
                    $this->email->from($this->settings->from_email_id,$this->settings->from_email_name);
                    $this->email->to($get_user_details->email);
                    $this->email->subject('Spera ' . $name . ' Assign for ' . $project_details->name . '');

                    $send_message = "Hi " . trim($get_user_details->firstname . " " . $get_user_details->lastname) . "<br/>
                                                    <p>Company_Name: " . $get_compnay->name . "</p><br/>
                                                    <p>Company_Logo: <img src='" . $company_logo . "' alt='image'/></p><br/>
                                                    <p>Task Link: " . $project_link . "</p><br/>
                                                    <p>Project Name: " . $project_details->name . "</p><br/>
                                                    <p>Project Description: " . $project_details->description . "</p><br/>
                                                    <p>Task Name: " . $name . "</p><br/>
                                                    <p>Task Description: " . $description . "</p><br/><br/><br/>
                                                    Thanks<br/>
                                                    Spera Team";
                    $this->email->message($send_message);
                    $mail_sent = null;
                    if ($this->email->send()) {
                        $mail_sent = 'Task Assign mail sent.';
                    }
                }
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Updated Successfully'));
                $this->response($newdata);
            }
            //else {
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Updated Successfully'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id or task id is not numeric'));
        $this->response($newdata);
    }

    /* Delete Task */

    function deletetask() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        //else { 
        //else {
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        //else {
        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $task_id = trim(htmlspecialchars($_REQUEST['task_id'])) ? trim(htmlspecialchars($_REQUEST['task_id'])) : '';
        if (empty($project_id) || empty($task_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        //else {
        if (is_numeric($project_id) && is_numeric($task_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }

                $check_task_for_project_or_not = $this->db->query('SELECT count(*) as count From project_has_tasks where project_id="' . $project_id . '" and id="' . $task_id . '"')->row_array();
                if ($check_task_for_project_or_not['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task not for This Project'));
                    $this->response($newdata);
                }
                $check_project_task_assign_user = $this->db->query("SELECT t.* FROM project_has_tasks t JOIN projects p ON t.project_id = p.id JOIN user_roles r ON t.user_id = r.user_id AND p.company_id = r.company_id WHERE t.project_id = '" . $project_id . "' AND r.company_id = '" . $company_id . "' AND r.role_id ='" . $role_id . "' and r.user_id='" . $this->user_id . "' and t.id='" . $task_id . "'UNION
            SELECT t.* from project_has_tasks t join project_assign_tasks at on t.id=at.task_id and t.project_id=at.project_id
            join user_roles r on at.assign_user_id=r.user_id where r.company_id='" . $company_id . "' and r.role_id='" . $role_id . "' and r.user_id='" . $this->user_id . "' AND t.project_id = '" . $project_id . "' and t.id='" . $task_id . "'
            ")->row_array();
                //echo "<pre>";print_r($check_project_task_assign_user);exit;
                if (empty($check_project_task_assign_user)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task Not Assign to This project to you'));
                    $this->response($newdata);
                }
                $tasks_delete_attachment = $this->db->query("SELECT * from project_has_tasks_attachment where task_id='" . $task_id . "'")->result_array();
                if (!empty($tasks_delete_attachment)) {
                    foreach ($tasks_delete_attachment as $kk => $vv) {
                        $path = FCPATH . 'files/tasks_attachment/' . $vv['task_attach_file'];
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                    $delete_task_attachment = $this->db->query('DELETE From project_has_tasks_attachment where task_id="' . $task_id . '"');
                }

                $delete_assign_users = ProjectAssignTasks::find('all', array('task_id' => $task_id));
                if (count($delete_assign_users) > 0) {
                    $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
                    $this->db->query($delete_task_assign_user);
                }
                $delete_task_comment = $this->db->query('DELETE From project_has_tasks_comment where task_id="' . $task_id . '" and project_id="' . $project_id . '"');
                $delete_task_timesheets = $this->db->query('DELETE From project_has_timesheets where task_id="' . $task_id . '" and project_id="' . $project_id . '"');
                $this->db->query('Delete from project_has_tasks where project_id="' . $project_id . '" and id="' . $task_id . '"');
                if ($this->db->_error_message()) {
                    $result = 'Error! [' . $this->db->_error_message() . ']';
                } else if (!$this->db->affected_rows()) {
                    $result = 'Error! ID [' . $task_id . '] not found';
                } else {
                    $result = 'Success';
                }
                if ($result == 'Success') {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Delete Task Successfully'));
                    $this->response($newdata);
                }
                //else {
                $newdata = array('result' => 'fail', 'response' => array('message' => $result, 'code' => 400));
                $this->response($newdata);
            }

            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }

            $check_project_task_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_tasks WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and task_id="' . $task_id . '"')->row_array();
            if ($check_project_task_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task Not Assign to You'));
                $this->response($newdata);
            }

            $check_task_for_project_or_not = $this->db->query('SELECT count(*) as count From project_has_tasks where project_id="' . $project_id . '" and id="' . $task_id . '"')->row_array();
            if ($check_task_for_project_or_not['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task not for This Project'));
                $this->response($newdata);
            }
            $tasks_delete_attachment = $this->db->query("SELECT * from project_has_tasks_attachment where task_id='" . $task_id . "'")->result_array();
            if (!empty($tasks_delete_attachment)) {
                foreach ($tasks_delete_attachment as $kk => $vv) {
                    $path = FCPATH . 'files/tasks_attachment/' . $vv['task_attach_file'];
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
                $delete_task_attachment = $this->db->query('DELETE From project_has_tasks_attachment where task_id="' . $task_id . '"');
            }

            $delete_assign_users = ProjectAssignTasks::find('all', array('task_id' => $task_id));
            if (count($delete_assign_users) > 0) {
                $delete_task_assign_user = "DELETE from project_assign_tasks where task_id='" . $task_id . "'";
                $this->db->query($delete_task_assign_user);
            }
            $delete_task_comment = $this->db->query('DELETE From project_has_tasks_comment where task_id="' . $task_id . '" and project_id="' . $project_id . '"');
            $delete_task_timesheets = $this->db->query('DELETE From project_has_timesheets where task_id="' . $task_id . '" and project_id="' . $project_id . '"');
            $this->db->query('Delete from project_has_tasks where project_id="' . $project_id . '" and id="' . $task_id . '"');
            if ($this->db->_error_message()) {
                $result = 'Error! [' . $this->db->_error_message() . ']';
            } else if (!$this->db->affected_rows()) {
                $result = 'Error! ID [' . $task_id . '] not found';
            } else {
                $result = 'Success';
            }
            if ($result == 'Success') {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Delete Task Successfully'));
                $this->response($newdata);
            }
            $newdata = array('result' => 'error', 'response' => array('message' => $result, 'code' => 400));
            $this->response($newdata);
        }

        $newdata = array('result' => 'error', 'response' => array('code' => 400, 'message' => 'Project id or task id is not numeric'));
        $this->response($newdata);
    }

    /* Add Milestone */

    function addmilestone() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        if (empty($project_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if (is_numeric($project_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : '';
                if (empty($name)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter milestone name'));
                    $this->response($newdata);
                }
                $start_date = trim(htmlspecialchars($_REQUEST['start_date'])) ? trim(htmlspecialchars($_REQUEST['start_date'])) : '';
                $due_date = trim(htmlspecialchars($_REQUEST['due_date'])) ? trim(htmlspecialchars($_REQUEST['due_date'])) : '';
                $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : '';
                $milestone_arr = array(
                    'project_id' => $project_id,
                    'name' => $name,
                    'start_date' => $start_date,
                    'due_date' => $due_date,
                    'description' => $description
                );
                $milestone = ProjectHasMilestone::create($milestone_arr);
                if (!$milestone) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Milestone create Unsuccessfully'));
                    $this->response($newdata);
                }
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project_id, 'milestone_id' => $milestone->id, 'message' => 'Milestone create Successfully'));
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : '';
            if (empty($name)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter milestone name'));
                $this->response($newdata);
            }
            $start_date = trim(htmlspecialchars($_REQUEST['start_date'])) ? trim(htmlspecialchars($_REQUEST['start_date'])) : '';
            $due_date = trim(htmlspecialchars($_REQUEST['due_date'])) ? trim(htmlspecialchars($_REQUEST['due_date'])) : '';
            $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : '';
            $milestone_arr = array(
                'project_id' => $project_id,
                'name' => $name,
                'start_date' => $start_date,
                'due_date' => $due_date,
                'description' => $description
            );
            $milestone = ProjectHasMilestone::create($milestone_arr);
            if (!$milestone) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Milestone create Unsuccessfully'));
                $this->response($newdata);
            }
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'project_id' => $project_id, 'milestone_id' => $milestone->id, 'message' => 'Milestone create Successfully'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id is not numeric'));
        $this->response($newdata);
    }

    /* Get Milestone */

    function getmilestone() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $milestone_id = trim(htmlspecialchars($_REQUEST['milestone_id'])) ? trim(htmlspecialchars($_REQUEST['milestone_id'])) : '';
        if (empty($project_id) || empty($milestone_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id) && is_numeric($milestone_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $milestone_details = ProjectHasMilestone::find_by_id($milestone_id);
                if (!$milestone_details) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'Milestone id not found'));
                    $this->response($newdata);
                }

                if ($milestone_details->description != '') {
                    $description = strip_tags($milestone_details->description);
                } else {
                    $description = "";
                }
                $milestone_arr = array(
                    'project_id' => $milestone_details->project_id,
                    'name' => $milestone_details->name,
                    'start_date' => $milestone_details->start_date,
                    'due_date' => $milestone_details->due_date,
                    'description' => $description
                );
                if (empty($milestone_arr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }
                $newdata = array('result' => 'success', 'response' => $milestone_arr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $milestone_details = ProjectHasMilestone::find_by_id($milestone_id);
            if (!$milestone_details) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'Milestone id not found'));
                $this->response($newdata);
            }

            if ($milestone_details->description != '') {
                $description = strip_tags($milestone_details->description);
            } else {
                $description = "";
            }
            $milestone_arr = array(
                'project_id' => $milestone_details->project_id,
                'name' => $milestone_details->name,
                'start_date' => $milestone_details->start_date,
                'due_date' => $milestone_details->due_date,
                'description' => $description
            );
            if (empty($milestone_arr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }
            $newdata = array('result' => 'success', 'response' => $milestone_arr, 'code' => 200);
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id or milestone id is not numeric'));
        $this->response($newdata);
    }

    /* Update Milestone */

    function updatemilestone() {

        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $milestone_id = trim(htmlspecialchars($_REQUEST['milestone_id'])) ? trim(htmlspecialchars($_REQUEST['milestone_id'])) : '';
        if (empty($project_id) || empty($milestone_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id) && is_numeric($milestone_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $milestone = ProjectHasMilestone::find($milestone_id);
                $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : $milestone->name;
                if (empty($name)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter milestone name'));
                    $this->response($newdata);
                }
                $start_date = trim(htmlspecialchars($_REQUEST['start_date'])) ? trim(htmlspecialchars($_REQUEST['start_date'])) : $milestone->start_date;
                $due_date = trim(htmlspecialchars($_REQUEST['due_date'])) ? trim(htmlspecialchars($_REQUEST['due_date'])) : $milestone->due_date;
                $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : $milestone->description;

                $milestone_arr = array(
                    'project_id' => $project_id,
                    'name' => $name,
                    'start_date' => $start_date,
                    'due_date' => $due_date,
                    'description' => $description
                );
                //var_dump($milestone_arr);exit;

                $milestone->update_attributes($milestone_arr);
                if (!$milestone) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Milestone updated Unsuccessfully'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Milestone updated Successfully'));
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $milestone = ProjectHasMilestone::find($milestone_id);
            $name = trim(htmlspecialchars($_REQUEST['name'])) ? trim(htmlspecialchars($_REQUEST['name'])) : $milestone->name;
            if (empty($name)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter milestone name'));
                $this->response($newdata);
            }
            $start_date = trim(htmlspecialchars($_REQUEST['start_date'])) ? trim(htmlspecialchars($_REQUEST['start_date'])) : $milestone->start_date;
            $due_date = trim(htmlspecialchars($_REQUEST['due_date'])) ? trim(htmlspecialchars($_REQUEST['due_date'])) : $milestone->due_date;
            $description = trim(htmlspecialchars($_REQUEST['description'])) ? trim(htmlspecialchars($_REQUEST['description'])) : $milestone->description;

            $milestone_arr = array(
                'project_id' => $project_id,
                'name' => $name,
                'start_date' => $start_date,
                'due_date' => $due_date,
                'description' => $description
            );
            //var_dump($milestone_arr);exit;

            $milestone->update_attributes($milestone_arr);
            if (!$milestone) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Milestone updated Unsuccessfully'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Milestone updated Successfully'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id or milestone id is not numeric'));
        $this->response($newdata);
    }

    /* Delete Milestone */

    function deletemilestone() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }
        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $milestone_id = trim(htmlspecialchars($_REQUEST['milestone_id'])) ? trim(htmlspecialchars($_REQUEST['milestone_id'])) : '';
        if (empty($project_id) || empty($milestone_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id) && is_numeric($milestone_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $milestone_delete_attachment = $this->db->query("SELECT * from project_has_milestones_attachment where milestone_id='" . $milestone_id . "'")->result_array();
                if (!empty($milestone_delete_attachment)) {
                    foreach ($milestone_delete_attachment as $kk => $vv) {
                        $path = FCPATH . 'files/milestone_attachment/' . $vv['milestone_attach_file'];
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                    $delete_mile_attachment = $this->db->query('DELETE From project_has_milestones_attachment where milestone_id="' . $milestone_id . '"');
                }
                $delete_milestone_comment = $this->db->query('DELETE From project_has_milestones_comment where milestone_id="' . $milestone_id . '"');
                $milestone = ProjectHasMilestone::find($milestone_id);
                foreach ($milestone->project_has_tasks as $value) {
                    $value->milestone_id = "";
                    $value->save();
                }
                $this->db->query('Delete from project_has_milestones where project_id="' . $project_id . '" and id="' . $milestone_id . '"');
                if ($this->db->_error_message()) {
                    $result = 'Error! [' . $this->db->_error_message() . ']';
                } else if (!$this->db->affected_rows()) {
                    $result = 'Error! ID [' . $milestone_id . '] not found';
                } else {
                    $result = 'Success';
                }
                if ($result == 'Success') {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Delete Milestone Successfully'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'fail', 'response' => array('message' => $result, 'code' => 400));
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $milestone_delete_attachment = $this->db->query("SELECT * from project_has_milestones_attachment where milestone_id='" . $milestone_id . "'")->result_array();
            if (!empty($milestone_delete_attachment)) {
                foreach ($milestone_delete_attachment as $kk => $vv) {
                    $path = FCPATH . 'files/milestone_attachment/' . $vv['milestone_attach_file'];
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
                $delete_mile_attachment = $this->db->query('DELETE From project_has_milestones_attachment where milestone_id="' . $milestone_id . '"');
            }
            $delete_milestone_comment = $this->db->query('DELETE From project_has_milestones_comment where milestone_id="' . $milestone_id . '"');
            $milestone = ProjectHasMilestone::find($milestone_id);
            foreach ($milestone->project_has_tasks as $value) {
                $value->milestone_id = "";
                $value->save();
            }
            $this->db->query('Delete from project_has_milestones where project_id="' . $project_id . '" and id="' . $milestone_id . '"');
            if ($this->db->_error_message()) {
                $result = 'Error! [' . $this->db->_error_message() . ']';
            } else if (!$this->db->affected_rows()) {
                $result = 'Error! ID [' . $milestone_id . '] not found';
            } else {
                $result = 'Success';
            }
            if ($result == 'Success') {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Delete Milestone Successfully'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'fail', 'response' => array('message' => $result, 'code' => 400));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id or milestone id is not numeric'));
        $this->response($newdata);
    }

    /* Project Timesheets By Task Id */

    function projecttimesheettaskid() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $task_id = trim(htmlspecialchars($_REQUEST['task_id'])) ? trim(htmlspecialchars($_REQUEST['task_id'])) : '';
        if (empty($project_id) || empty($task_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id) && is_numeric($task_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                $alltimesheets = ProjectHasTimesheet::find("all", array("conditions" => array("task_id = ?", $task_id)));
                if (empty($alltimesheets)) {
                    $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No timesheets for this project and task'));
                    $this->response($newdata);
                }

                $newArr = array();
                $i = 0;
                foreach ($alltimesheets as $key => $value) {
                    $tracking = floor($value->time / 60);
                    $tracking_hours = floor($tracking / 60);
                    $tracking_minutes = $tracking - ($tracking_hours * 60);
                    $time_spent = $tracking_hours . " Hours " . $tracking_minutes . " Minutes";
                    $newArr[$i]['id'] = $value->id;
                    $newArr[$i]['project_id'] = $project_id;
                    $newArr[$i]['task_id'] = $task_id;
                    $newArr[$i]['time_spent'] = $time_spent;
                    $newArr[$i]['start_date'] = date('Y-m-d', strtotime($value->start));
                    $newArr[$i]['end_date'] = date('Y-m-d', strtotime($value->end));
                    if ($value->description != '') {
                        $newArr[$i]['description'] = strip_tags($value->description);
                    } else {
                        $newArr[$i]['description'] = "";
                    }
                    $i++;
                }
                if (empty($newArr)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                    $this->response($newdata);
                }

                $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 200);
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }

            $check_project_task_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_tasks WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and task_id="' . $task_id . '"')->row_array();
            if ($check_project_task_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task Not Assign to You'));
                $this->response($newdata);
            }
            $alltimesheets = ProjectHasTimesheet::find("all", array("conditions" => array("task_id = ?", $task_id)));
            if (empty($alltimesheets)) {
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'No timesheets for this project and task'));
                $this->response($newdata);
            }

            $newArr = array();
            $i = 0;
            foreach ($alltimesheets as $key => $value) {
                $tracking = floor($value->time / 60);
                $tracking_hours = floor($tracking / 60);
                $tracking_minutes = $tracking - ($tracking_hours * 60);
                $time_spent = $tracking_hours . " Hours " . $tracking_minutes . " Minutes";
                $newArr[$i]['id'] = $value->id;
                $newArr[$i]['project_id'] = $project_id;
                $newArr[$i]['task_id'] = $task_id;
                $newArr[$i]['time_spent'] = $time_spent;
                $newArr[$i]['start_date'] = date('Y-m-d', strtotime($value->start));
                $newArr[$i]['end_date'] = date('Y-m-d', strtotime($value->end));
                if ($value->description != '') {
                    $newArr[$i]['description'] = strip_tags($value->description);
                } else {
                    $newArr[$i]['description'] = "";
                }
                $i++;
            }
            if (empty($newArr)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
                $this->response($newdata);
            }

            $newdata = array('result' => 'success', 'response' => $newArr, 'code' => 400);
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id or task id is not numeric'));
        $this->response($newdata);
    }

   /* Add comment On Task By Project */

    function addcommentontaskbyproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $task_id = trim(htmlspecialchars($_REQUEST['task_id'])) ? trim(htmlspecialchars($_REQUEST['task_id'])) : '';
        if (empty($project_id) || empty($task_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id) && is_numeric($task_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            $user_id = $get_data['user_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                
                $check_task_for_project=$this->db->query('SELECT * FROM project_has_tasks t JOIN projects p ON t.project_id = p.id WHERE t.project_id ="' . $project_id . '" AND t.id ="' . $task_id . '" AND p.company_id ="' . $company_id . '"')->row_array();
                if(empty($check_task_for_project))
                {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task not for this project'));
                    $this->response($newdata);
                }
                $comment = trim(htmlspecialchars($_REQUEST['comment'])) ? trim(htmlspecialchars($_REQUEST['comment'])) : '';

                if (empty($comment)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter comment for task'));
                    $this->response($newdata);
                }

                $comment_arr['message'] = strip_tags($comment);
                $comment_arr['project_id'] = $project_id;
                $comment_arr['company_id'] = $company_id;
                $comment_arr['task_id'] = $task_id;
                $comment_arr['user_id'] = $user_id;
                $comment_arr['datetime'] = time();

                $commentAdded = ProjectHasTasksComment::create($comment_arr);
                if (!$commentAdded) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Task Comment Added Unsuccessfully'));
                    $this->response($newdata);
                }

                $subject = "task-comment";
                $ac_message = strip_tags($comment);
                $user_id = $user_id;
                $datetime = time();
                $type = "comment";
                $activity_arr = array(
                    "subject" => $subject,
                    "message" => $ac_message,
                    "project_id" => $project_id,
                    "user_id" => $user_id,
                    "datetime" => $datetime,
                    "type" => $type
                );
                //echo "<pre>";print_r($activity_arr);exit;
                $activity = ProjectHasActivity::create($activity_arr);

                if (!$activity) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Activity Added Unsuccessfully'));
                    $this->response($newdata);
                }
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Comment Added Successfully'));
                $this->response($newdata);
            }
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }

            $check_project_task_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_tasks WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and task_id="' . $task_id . '"')->row_array();
            if ($check_project_task_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task Not Assign to You'));
                $this->response($newdata);
            }
            
            $check_task_for_project=$this->db->query('SELECT * FROM project_has_tasks t JOIN projects p ON t.project_id = p.id WHERE t.project_id ="' . $project_id . '" AND t.id ="' . $task_id . '" AND p.company_id ="' . $company_id . '"')->row_array();
            if(empty($check_task_for_project))
            {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Task not for this project'));
                $this->response($newdata);
            }
            
            $comment = trim(htmlspecialchars($_REQUEST['comment'])) ? trim(htmlspecialchars($_REQUEST['comment'])) : '';
            if (empty($comment)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter comment for task'));
                $this->response($newdata);
            }
            $comment_arr['message'] = strip_tags($comment);
            $comment_arr['project_id'] = $project_id;
            $comment_arr['company_id'] = $company_id;
            $comment_arr['task_id'] = $task_id;
            $comment_arr['user_id'] = $user_id;
            $comment_arr['datetime'] = time();

            $commentAdded = ProjectHasTasksComment::create($comment_arr);
            if (!$commentAdded) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Task Comment Added Unsuccessfully'));
                $this->response($newdata);
            }

            $subject = "task-comment";
            $ac_message = strip_tags($comment);
            $user_id = $user_id;
            $datetime = time();
            $type = "comment";
            $activity_arr = array(
                "subject" => $subject,
                "message" => $ac_message,
                "project_id" => $project_id,
                "user_id" => $user_id,
                "datetime" => $datetime,
                "type" => $type
            );

            $activity = ProjectHasActivity::create($activity_arr);

            if (!$activity) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Activity Added Unsuccessfully'));
                $this->response($newdata);
            }
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Task Comment Added Successfully'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id or task id is not numeric'));
        $this->response($newdata);
    }

    /* Add comment On Milestone By Project */

    function addcommentonmilesotnebyproject() {
        $newdata = array();
        if (empty($_REQUEST)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if ($this->user_id == 0) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        $project_id = trim(htmlspecialchars($_REQUEST['project_id'])) ? trim(htmlspecialchars($_REQUEST['project_id'])) : '';
        $milestone_id = trim(htmlspecialchars($_REQUEST['milestone_id'])) ? trim(htmlspecialchars($_REQUEST['milestone_id'])) : '';
        if (empty($project_id) || empty($milestone_id)) {
            $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'No Data Found'));
            $this->response($newdata);
        }

        if (is_numeric($project_id) && is_numeric($milestone_id)) {
            $get_data = $this->db->query('SELECT r . * , u.email, u.firstname, u.lastname, u.email FROM user_roles r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = "' . $this->user_id . '"')->row_array();
            if (empty($get_data)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 404, 'message' => 'User data not found'));
                $this->response($newdata);
            }
            $role_id = $get_data['role_id'];
            $company_id = $get_data['company_id'];
            $user_id = $get_data['user_id'];
            if ($role_id == 2) {
                $check_project_assign = $this->db->query('SELECT count(*) as count from projects where id="' . $project_id . '" and company_id="' . $company_id . '"')->row_array();
                if ($check_project_assign['count'] != 1) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                    $this->response($newdata);
                }
                
                $check_milestone_project=$this->db->query('SELECT * FROM project_has_milestones m JOIN projects p ON m.project_id = p.id WHERE m.project_id ="' .                                          $project_id . '" AND m.id ="' . $milestone_id . '" AND p.company_id ="'.$company_id .'"')->row_array();
                if(empty($check_milestone_project))
                {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Milestone not for this project'));
                    $this->response($newdata);
                }
                $comment = trim(htmlspecialchars($_REQUEST['comment'])) ? trim(htmlspecialchars($_REQUEST['comment'])) : '';

                if (empty($comment)) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter comment for milestone'));
                    $this->response($newdata);
                }

                $comment_arr['message'] = strip_tags($comment);
                $comment_arr['project_id'] = $project_id;
                $comment_arr['company_id'] = $company_id;
                $comment_arr['milestone_id'] = $milestone_id;
                $comment_arr['user_id'] = $user_id;
                $comment_arr['datetime'] = time();

                $commentAdded = ProjectHasMilestonesComment::create($comment_arr);
                if (!$commentAdded) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Milestone Comment Added Unsuccessfully'));
                    $this->response($newdata);
                }

                $subject = "milestone-comment";
                $ac_message = strip_tags($comment);
                $user_id = $user_id;
                $datetime = time();
                $type = "comment";
                $activity_arr = array(
                    "subject" => $subject,
                    "message" => $ac_message,
                    "project_id" => $project_id,
                    "user_id" => $user_id,
                    "datetime" => $datetime,
                    "type" => $type
                );

                $activity = ProjectHasActivity::create($activity_arr);

                if (!$activity) {
                    $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Activity Added Unsuccessfully'));
                    $this->response($newdata);
                }
                $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Milestone Comment Added Successfully'));
                $this->response($newdata);
            }
            
            $check_project_assign_user = $this->db->query('SELECT count( * ) AS count FROM project_assign_clients WHERE project_id ="' . $project_id . '" AND assign_user_id ="' . $this->user_id . '" and company_id="' . $get_data['company_id'] . '"')->row_array();
            if ($check_project_assign_user['count'] != 1) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Not Assign To You'));
                $this->response($newdata);
            }
            $check_milestone_project=$this->db->query('SELECT * FROM project_has_milestones m JOIN projects p ON m.project_id = p.id WHERE m.project_id ="' .                                          $project_id . '" AND m.id ="' . $milestone_id . '" AND p.company_id ="'.$company_id .'"')->row_array();
            if(empty($check_milestone_project))
            {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'This Milestone not for this project'));
                $this->response($newdata);
            }
            $comment = trim(htmlspecialchars($_REQUEST['comment'])) ? trim(htmlspecialchars($_REQUEST['comment'])) : '';

            if (empty($comment)) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Please enter comment for milestone'));
                $this->response($newdata);
            }

            $comment_arr['message'] = strip_tags($comment);
            $comment_arr['project_id'] = $project_id;
            $comment_arr['company_id'] = $company_id;
            $comment_arr['milestone_id'] = $milestone_id;
            $comment_arr['user_id'] = $user_id;
            $comment_arr['datetime'] = time();

            $commentAdded = ProjectHasMilestonesComment::create($comment_arr);
            if (!$commentAdded) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Milestone Comment Added Unsuccessfully'));
                $this->response($newdata);
            }

            $subject = "milestone-comment";
            $ac_message = strip_tags($comment);
            $user_id = $user_id;
            $datetime = time();
            $type = "comment";
            $activity_arr = array(
                "subject" => $subject,
                "message" => $ac_message,
                "project_id" => $project_id,
                "user_id" => $user_id,
                "datetime" => $datetime,
                "type" => $type
            );

            $activity = ProjectHasActivity::create($activity_arr);

            if (!$activity) {
                $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project Activity Added Unsuccessfully'));
                $this->response($newdata);
            }
            $newdata = array('result' => 'success', 'response' => array('code' => 200, 'message' => 'Milestone Comment Added Successfully'));
            $this->response($newdata);
        }
        $newdata = array('result' => 'fail', 'response' => array('code' => 400, 'message' => 'Project id or milestone id is not numeric'));
        $this->response($newdata);
    }

}
