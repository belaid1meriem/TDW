<?php
abstract class Model{
    protected PDO $db;
    protected string $table;
    public function __construct(string $table)
    {
        $this->db = Database::getConnection();
        $this->table=$table;
    }



    
    public function create($fields, $values)
    {
        $dotted_fields= array_map(fn($f)=>":$f",$fields);
        $sql = "INSERT INTO".$this->table."(". implode(",", $fields). " VALUES ". "(". implode(",",$dotted_fields);
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_combine($fields, $values));
    }

    public function select(){

    }

    public function update(){

    }

    public function delete(){

    }


}