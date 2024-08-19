<?php

new CodeGenerator($argv);

class CodeGenerator {

    public $argv;

    public $commands = [
        [
            "help",
        ],
        [
            "generate [type] [class name] [methods]...",
            "Usage: generate class TestClass index,create,view",
            "Types available: class"
        ]
    ];

    function __construct($argv)
    {
        $this->argv = $argv;

        if(!isset($this->argv[1])) {
            $this->showMessage("Specify a command: \"generate\"");
        }

        $command = strtolower($this->argv[1]);

        if(!method_exists($this, $command)) {
            $this->showMessage("Command does not exist. Try command \"generate\"");
        }

        $this->$command();
    }
    
    function help()
    {
        echo "\n";
        foreach ($this->commands as $section) {
            foreach ($section as $line) {
                echo $section[0] === $line ? ">> " : "   ";
                echo "$line\n";
            }
            if($section !== end($this->commands)) {
                echo "\n";
            }
        }
    }

    function generate()
    {
        if(!isset($this->argv[2])) {
            $this->showMessage("Specify a type to generate. Try \"class\"");
        }

        $command = "generate" . strtolower(ucfirst($this->argv[2]));

        if(!method_exists($this, $command)) {
            $this->showMessage("Type does not exist. Try command \"class\"");
        }

        $this->$command();
    }

    function generateClass()
    {
        if(!isset($this->argv[3])) {
            $this->showMessage("Specify a class name");
        }

        $name = $this->argv[3];

        if(file_exists("$name.php")) {
            $this->showMessage("Class already exists");
        }

        $lines = file("templates/class.php");
        $line = $this->findLineByString($lines, "class");
        if(!$line) {
            $this->showMessage("Something went wrong");
        }

        $lines[$line] = "class $name\n";

        $lines = $this->generateMethods($lines);

        file_put_contents(
            "$name.php",
            $lines
        );
    }

    function generateMethods($classLines)
    {
        if(!isset($this->argv[4])) {
            return $classLines;
        }

        $endOfClass = $this->findLineByString($classLines, "}");
        unset($classLines[$endOfClass]);

        $methods = explode(",", $this->argv[4]);
        $lines = file("templates/method.php");
        $declarationLine = $this->findLineByString($lines, "function");

        foreach($methods as $method) {
            $methodLines = $lines;
            $methodLines[$declarationLine] = "    function $method()\n";
            $methodLines[] = "\n";

            $classLines = array_merge($classLines, $methodLines);
            $classLines[] = "\n";
        }

        $classLines[] = "}";

        return $classLines;
    }

    function findLineByString($lines, $string)
    {
        foreach ($lines as $index => $line) {
            if(str_contains($line, $string)) {
                return $index;
            }
        }

        return null;
    }

    function showMessage($message)
    {
        echo $message;
        exit;
    }
}