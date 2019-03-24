<?php

class Home extends Controller
{
    private $file;
    private $countRecords = 10;

    public function __construct()
    {
        parent::__construct();
        $this->className = strtolower(__CLASS__) . '/';
    }

    public function index()
    {
        $userInfo = $this->getUserInfo();
        $isAdmin = $this->isInRole("Admin");
        $this->saveVisitInformation("mainPage");
        $this->view->generate($this->className . 'mainPage', compact("userInfo", "isAdmin"));
    }

    public function aboutMe()
    {
        $this->view->generate($this->className . 'aboutMe');
    }

    public function myInterests()
    {
        $this->view->generate($this->className . 'myInterests');
    }

    public function photoalbum()
    {
        $this->view->generate($this->className . 'photoalbum');
    }

    public function education()
    {
        $this->view->generate($this->className . 'education');
    }

    public function admin()
    {
        $this->view->generate($this->className . 'admin');
    }

    public function visits()
    {
        $this->view->generate($this->className . 'visits');
    }


    public function guest_book()
    {
        if (!$this->isInRole("Admin")) {
            $this->view->generate('401');
            return null;
        }

        if (!empty($_POST)) {
            $this->file = fopen(APP_PATH . "public/files/message.inc", "a");
            $string = "\n" . date("d.m.y") . ";" .
                $_POST["FIO"] . ";" .
                $_POST["email"] . ";" .
                $_POST["question"];
            fwrite($this->file, $string);
            fclose($this->file);
        }
        $reviews = [];
        $this->file = fopen(APP_PATH . "public/files/message.inc", "r");
        while (!feof($this->file)) {
            $row = fgets($this->file);
            $message = explode(";", $row);
            $review = new ReviewModel($message[0], $message[1], $message[2], $message[3]);
            array_push($reviews, $review);
        }
        fclose($this->file);
        usort($reviews, function ($a, $b) {
            if ($a->Date == $b->Date) {
                return 0;
            }
            return ($a->Date < $b->Date) ? 1 : -1;
        });
        $this->view->generate($this->className . 'guest_book', compact("reviews"));
    }

    public function loadRecordsFromFile()
    {
        if (!$this->isInRole("Admin")) {
            $this->view->generate('401');
            return null;
        }
        if (!empty($_FILES)) {
            $uploadDir = APP_PATH . "public/files/tmp/";
            $uploadFile = $uploadDir . basename($_FILES['records']['name']);
            if (move_uploaded_file($_FILES['records']['tmp_name'], $uploadFile)) {
                $file = fopen($uploadFile, "r");
                print_r($file);
                $this->file = fopen(APP_PATH . "public/files/message.inc", "a");
                fwrite($this->file, "\n");
                while (!feof($file)) {
                    $message = fgets($file);
                    print_r($message);
                    fwrite($this->file, $message);
                }
                fclose($file);
                fclose($this->file);
                unlink($uploadFile);
            } else {
                echo "Не удалось загрузить файл";
            }
//            header("Location: " . "/guestBook");
        }
        $this->view->generate($this->className . 'loadRecordsFromFile');
    }

    public function contacts()
    {
        $errors = [];
        if (!empty($_POST)) {
            $errors = Validation::run([
                'FIO' => 'required',
                'pol' => 'required',
                'dateOfBirth' => 'required',
                'email' => 'email',
                'tel' => 'tel'
            ]);
        }
        $this->view->generate($this->className . 'contacts', compact('errors'));
    }

    public function test()
    {
        if (!$this->isInRole("Admin")) {
            $this->view->generate('401');
            return null;
        }
        $errors = [];
        $responses = [];
        if (!empty($_POST)) {
            $errors = TestValidation::run([
                'FIO' => 'required,fio',
                'groups' => 'required',
                'answer1' => 'required',
                'answer2' => 'required',
                'answer3' => 'required'
            ]);
            $responses = TestVerification::run([
                'answer1' => 'answer-one',
                'answer2' => 'answer-two',
                'answer3' => 'answer-three'
            ]);
            if (empty($errors)) {
                $model = new TestsModel();
                $model->Fio = $_POST['FIO'];
                $model->Group = $_POST['groups'];
                $model->Answer1 = $_POST['answer1'];
                $model->Answer2 = $_POST['answer2'];
                $model->Answer3 = $_POST['answer3'];
                $model->IsAnswer1 = empty($responses['answer1']);
                $model->IsAnswer2 = empty($responses['answer2']);
                $model->IsAnswer3 = empty($responses['answer3']);
                $model->save();
            }
        }
        $this->view->generate($this->className . 'test', compact('errors', 'responses'));
    }

    public function table() {
        $models = TestsModel::getAll();
        $this->view->generate($this->className . 'table', compact('models'));
    }

    public function history()
    {
        $this->view->generate($this->className . 'history');
    }

    public function blog()
    {
        if(empty($_GET["page"])) {
            $page = 0;
        } else {
            $page = $_GET["page"];
        }
        $records = BlogModel::paginate($page, $this->countRecords);
        $count = BlogModel::getCount();
        $countPages = round($count / $this->countRecords);
        $this->view->generate($this->className . 'blog', compact("records", "countPages", "page"));
    }

    public function editBlog()
    {
        $errors = [];
        if(!empty($_POST)) {
            if(strcmp($_POST['mode'], 'manual') == 0) {
                $errors = Validation::run([
                    'topic' => 'required',
                    'author' => 'required',
                    'message' => 'required',
                ]);
                if(empty($errors)) {
                    $model = new BlogModel();
                    $model->Topic = $_POST['topic'];
                    $model->Author =  $_POST['author'];
                    $model->Message =  $_POST['message'];
                    if(is_uploaded_file($_FILES['photo']['tmp_name'])) {
                        $extension = pathinfo($_FILES['photo']['name'])['extension'];
                        $model->PathToPhoto = '/img/blog/' . hash("sha256", time()) . "." . $extension;
                        $path = APP_PATH . 'public' . $model->PathToPhoto;
                        move_uploaded_file($_FILES['photo']['tmp_name'], $path);
                    }
                    $model->save();
                }

            } else {
                if(is_uploaded_file($_FILES['records']['tmp_name'])) {
                    $file = fopen($_FILES['records']['tmp_name'], "r");
                    $model = new BlogModel();
                    while(!feof($file)) {
                        $data = explode(";", fgetcsv($file)[0]);
                        $model->Topic = $data[0];
                        $model->Message = $data[1];
                        $model->Author = $data[2];
                        $model->CurrentDate = $data[3];
                        $model->save();
                    }
                    fclose($file);
                }
            }
            header("Location: " . "/blog");
        }
        $this->view->generate($this->className . 'editBlog', compact($errors));
    }

    public static function showList($list, $type, $class, $ob) {
        if ($type == 0) {
            echo "<ul class = ". $class . " " ." type = ".$ob. ">";
        }
        else
            if ($type == 1) {
                echo "<ol class = ". $class . " " ."type = ".$ob. ">";
            }
        for ($i = 0; $i < count($list); $i++) {
            echo "<li>$list[$i]</li>";
        }
        if ($type == 0) {
            echo '</ol>';
        }  else {
            echo '</ul>';
        }
    }

    public static function photoGallery(){
        $hrefs = array("img/cirilla.jpg", "img/doctor_who.jpg", "img/dovakin.jpg", "img/geralt.jpg", "img/illidan.jpg",
            "img/jaina.jpeg", "img/Jean.jpeg", "img/konstantin.jpg", "img/legolas.jpg", "img/neo.jpg","img/ragnaros.jpg",
            "img/silvana.jpeg", "img/tauriel.jpg", "img/thrall.jpg", "img/tiranda.png");
        $srcs = array("img/cirilla-sm.jpg", "img/doctor_who-sm.jpg", "img/dovakin-sm.jpg", "img/geralt-sm.jpg", "img/illidan-sm.jpg",
            "img/jaina-sm.jpg", "img/Jean-sm.jpg", "img/konstantin-sm.jpg", "img/legolas-sm.jpg", "img/neo-sm.jpg","img/ragnaros-sm.jpg",
            "img/silvana-sm.jpg", "img/tauriel-sm.jpg", "img/thrall-sm.jpg", "img/tiranda-sm.png");
        $alts = array("Цирилла","Доктор Кто","Довакин","Геральт","Иллидан",
            "Джайна Праудмур","Джин Грей","Джон Константин","Леголас","Нео",
            "Рагнарос","Сильвана Ветрокрылая","Тауриэль","Тралл","Тиранда Шелест Ветра");
        for ($i = 0; $i < count($alts); $i++) {
            echo "<li><a href=$hrefs[$i]  title=$alts[$i]> <img src=$srcs[$i] alt=$alts[$i]>$alts[$i] </a></li>";
        }
    }

    public function login()
    {
        $errors = [];
        if (!empty($_POST)) {
            $errors = TestValidation::run([
                "login" => "required",
                "password" => "required"
            ]);
            if (empty($errors)) {
                $user = UserModel::getUser($_POST['login'], hash("sha256", $_POST['password']));
                if ($user == null) {
                    $loginError = "Неверный логин либо пароль, попробуйте еще раз!";
                } else {
                    $_SESSION["user"] = $user;
                    header("Location: " . "/");
                    exit;
                }
            }
        }
        $this->saveVisitInformation("login");
        $this->view->generate($this->className . 'login', compact("errors", "loginError"));
    }

    public function register()
    {
        $errors = [];
        if (!empty($_POST)) {
            $errors = TestValidation::run([
                "login" => "required,login",
                "password" => "required",
                "fio" => "required,fio",
                "email" => "email"
            ]);
            if (empty($errors)) {
                $user = new UserModel();
                $user->Login = $_POST['login'];
                $user->Password = hash("sha256", $_POST['password']);
                $user->Fio = $_POST['fio'];
                $user->Email = $_POST['email'];
                $user->Role_Id = 2; //Обычный пользователь
                $user->save();
            }
        }
        $this->saveVisitInformation("register");
        $this->view->generate($this->className . 'register', compact("errors"));
    }

    public function logout()
    {
        session_destroy();
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    public static function showError($error)
    {
        echo $error;
    }
}