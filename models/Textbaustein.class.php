<?php
class Textbaustein
{
    public static function loadAll()
    {
        $query = "SELECT textbaustein_id, code, title, content, mkdate, chdate, user_id
                  FROM stg_textbausteine
                  ORDER BY code";
        $statement = DBManager::get()->prepare($query);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function load($id)
    {
        $query = "SELECT textbaustein_id, code, title, content, mkdate, chdate, user_id
                  FROM stg_textbausteine
                  WHERE textbaustein_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public static function store($id, $code, $title, $content)
    {
        $query = "INSERT INTO stg_textbausteine
                    (textbaustein_id, code, title, content, mkdate, chdate, user_id)
                  VALUES (:id, :code, :title, :content, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :user_id)
                  ON DUPLICATE KEY UPDATE code = VALUES(code), title = VALUES(title),
                    content = VALUES(content), chdate = UNIX_TIMESTAMP()";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':code', $code);
        $statement->bindValue(':title', $title);
        $statement->bindValue(':content', $content);
        $statement->bindValue(':user_id', $GLOBALS['auth']->auth['uid']);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    public static function delete($id)
    {
        $query = "DELETE FROM stg_textbausteine
                  WHERE textbaustein_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();

        return $statement->rowCount() > 0;
    }
}
