<?php
    function init()
    {
        $fjosn = file_get_contents('builder.json', 'r');
        $json = json_decode($fjosn, true);
        $padrao = [
            "controller",
            "bo",
            "dao",
            "dto",
            "view",
            "conexao"
        ];
        $folders = array_merge($padrao, $json['folders']);
        criarDiretorios($folders);
        criarAutoload($folders);
        criarPDO($json['pdo']);
        criarController();
        criarIndex();
    }
    function criarDiretorios($folders)
    {
        foreach ($folders as $key => $value) {
            if (!file_exists($value)) {
                mkdir($value, 0700);
            }
        }
    }
    function criarAutoload($folders)
    {
        $autoload = "<?php\n    spl_autoload_register(function (\$nomeClasse) {\n        \$folders = array(\"".implode("\", \"", $folders)."\");
        foreach (\$folders as \$folder)
            if (file_exists(__DIR__.DIRECTORY_SEPARATOR.\$folder.DIRECTORY_SEPARATOR.\$nomeClasse.\".php\"))
                require_once(__DIR__.DIRECTORY_SEPARATOR.\$folder.DIRECTORY_SEPARATOR.\$nomeClasse.\".php\");\n    });\n?>";
        $fp = fopen('autoload.php', 'w');
        fwrite($fp, $autoload);
        fclose($fp);
    }
    function criarPDO($pdo)
    {
        $pdo = "<?php\n    class Conexao{
        public static \$instance;
        private function __construct()
        {}
        public static function getInstance()
        {
            if (!isset(self::\$instance)) 
            {
                self::\$instance = new PDO('".$pdo['driver'].":host=".$pdo['host'].";dbname=".$pdo['dbName']."', \"".$pdo['username']."\",\"".$pdo['password']."\");
                self::\$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return self::\$instance;
        }\n    }\n?>";
        $fp = fopen('conexao/Conexao.php', 'w');
        fwrite($fp, $pdo);
        fclose($fp);
    }
    function criarController()
    {
        $controller = "<?php\n    require_once \"../autoload.php\";\n    class Controller\n    {
        public function getPdo()
        {
            return Conexao::getInstance();
        }\n    }\n?>";
        $fp = fopen('controller/Controller.php', 'w');
        fwrite($fp, $controller);
        fclose($fp);
    }
    function criarIndex()
    {
        $index = "<?php\n    require_once \"../autoload.php\";\n    \$control = new Controller;\n    \$pdo = null;".
        "\n    \$pdo = \$control->getPdo();".
        "\n    if(\$pdo != null){".
        "\n        echo \"Funcionou\";\n    }else {\n        echo \"Falhou\";\n    }\n?>";
        $fp = fopen('view/index.php', 'w');
        fwrite($fp, $index);
        fclose($fp);
    }
    init();
    header("Location: view/index.php");
 ?>