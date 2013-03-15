<?php
class Textbaustein
{
    public static function loadAll($language = null)
    {
        $query = "SELECT textbaustein_id, code, language, title, content, mkdate, chdate, user_id
                  FROM stg_textbausteine
                  WHERE language = IFNULL(:language, language)
                  ORDER BY code";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':language', $language);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function load($id)
    {
        $query = "SELECT textbaustein_id, code, language, title, content, mkdate, chdate, user_id
                  FROM stg_textbausteine
                  WHERE textbaustein_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public static function loadByCode($code)
    {
        $query = "SELECT textbaustein_id, code, language, title, content, mkdate, chdate, user_id
                  FROM stg_textbausteine
                  WHERE code = :code";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':code', $code);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public static function store($id, $code, $language, $title, $content)
    {
        $query = "INSERT INTO stg_textbausteine
                    (textbaustein_id, code, language, title, content, mkdate, chdate, user_id)
                  VALUES (:id, :code, :language, :title, :content, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :user_id)
                  ON DUPLICATE KEY UPDATE code = VALUES(code), language = VALUES(language),
                    title = VALUES(title), content = VALUES(content), chdate = UNIX_TIMESTAMP()";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':code', $code);
        $statement->bindValue(':language', $language);
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

//

    public static function removeCombination($profil_id)
    {
        $query = "DELETE FROM stg_textcombinations
                  WHERE profil_id = :profil_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':profil_id', $profil_id);
        $statement->execute();
    }

    public static function addCombination($profil_id, $code, $ids)
    {
        $query = "INSERT INTO stg_textcombinations (profil_id, code, position, textbaustein_id, semester, restriction)
                  VALUES (:profil_id, :code, :position, :textbaustein_id, :semester, :restriction)";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':profil_id', $profil_id);
        $statement->bindValue(':code', $code);

        $position = 0;
        foreach ($ids as $id => $data) {
            $statement->bindValue(':position', $position);
            $statement->bindValue(':textbaustein_id', $id);
            $statement->bindValue(':semester', $data['semester']);
            $statement->bindValue(':restriction', $data['restriction']);
            $statement->execute();

            $position += 1;
        }
    }

    public static function loadCombination($profil_id, $code = null)
    {
        $query = "SELECT tc.code, textbaustein_id, semester, restriction, tb.code AS tb_code, title
                  FROM stg_textcombinations AS tc
                  JOIN stg_textbausteine AS tb USING (textbaustein_id)
                  WHERE profil_id = :profil_id AND tc.code = IFNULL(:code, tc.code)
                  ORDER BY tc.code ASC, position ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':profil_id', $profil_id);
        $statement->bindValue(':code', $code);
        $statement->execute();

        $temp = $statement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        return $code === null
             ? $temp
             : (array)@$temp[$code];
    }

    public static function render($code, $variables = array())
    {
        $replaces = array();
        if (preg_match_all('/\#\{textbaustein:(.*?)\}/', $code, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $temp = Textbaustein::loadByCode($match[1]);
                $replaces[$match[0]] = Textbaustein::render($temp['content'], $variables);
            }
        }

        foreach ($variables as $key => $value) {
            $key = sprintf('#{%s}', $key);
            $replaces[$key] = Textbaustein::render($value);
        }

        $result = str_replace(array_keys($replaces), array_values($replaces), $code);

        return $result;
    }
}
