<?php

namespace Michcald\Dummy;

class Repository
{
    private $name;
    
    private $description;
    
    private $singularLabel;
    
    private $pluralLabel;
    
    private $fields = array();
    
    private $db;
    
    private $parents = array();
    
    private $children = array();

    public function __construct($name)
    {
        $this->name = $name;
        
        $id = new Entity\Field\Integer('id');
        // add validation
        $this->addField($id);
    }

    public function addField(Entity\Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setDb(\Michcald\Db\Adapter $db)
    {
        $this->db = $db;
        
        return $this;
    }
    
    public function getDb()
    {
        return $this->db;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setSingularLabel($singularLabel)
    {
        $this->singularLabel = $singularLabel;
        
        return $this;
    }
    
    public function getSingularLabel()
    {
        return $this->singularLabel;
    }
    
    public function setPluralLabel($pluralLabel)
    {
        $this->pluralLabel = $pluralLabel;
        
        return $this;
    }
    
    public function getPluralLabel()
    {
        return $this->pluralLabel;
    }

    public function addParent($parent)
    {
        $this->parents[] = $parent;
        
        $name = $parent . '_id';
        
        $field = new Entity\Field\Integer($name);
        $field->setLabel($parent)
                ->setRequired(true);
        
        $this->addField($field);
        
        return $this;
    }
    
    public function getParents()
    {
        return $this->parents;
    }

    public function addChild($child)
    {
        $this->children[] = $child;
        
        return $this;
    }
    
    public function getChildren()
    {
        return $this->children;
    }

    public function count()
    {
        return $this->getDb()->fetchOne(
                'SELECT COUNT(id) FROM ' . $this->getName());
    }

    private function validate(Entity $entity)
    {
        foreach ($this->fields as $field) {

            $fieldName = $field->getName();

            if ($fieldName == 'id') {
                continue;
            }

            if (!$field->validate($entity->$fieldName)) {
                return false;
            }
        }

        return true;
    }

    public function create(array $data = null)
    {
        $entity = new Entity($this);

        if (is_array($data)) {
            foreach ($this->fields as $field) {
                $fieldName = $field->getName();
                if (array_key_exists($field->getName(), $data)) {
                    $entity->$fieldName = $data[$fieldName];
                    // TODO gestire tipo file
                } else {
                    if ($field->isRequired()) {
                        throw new \Exception('Field required: ' . $fieldName);
                    }
                }
            }
        }
        
        return $entity;
    }

    public function findOne($id)
    {
        $row = $this->getDb()->fetchRow(
            'SELECT * FROM ' . $this->getName() . ' WHERE id=?', $id);

        if (!$row) {
            return false;
        }

        $entity = $this->create();

        foreach ($row as $key => $value) {
            $entity->$key = $value;
        }

        return $entity;
    }
    
    public function findAll($order, $limit, $offset)
    {
        $rows = $this->getDb()->fetchAll(
            'SELECT * FROM ' . $this->getName() . ' ORDER BY ' . $order
                . ' LIMIT ' . $limit . ' OFFSET ' . $offset
        );
        
        $entities = array();
        
        foreach ($rows as $row) {
            $entity = $this->create();
            foreach ($row as $key => $value) {
                $entity->$key = $value;
            }
            $entities[] = $entity;
        }
        
        return $entities;
    }
    
    public function findBy(array $where, $query, $order, $limit, $offset)
    {
        $sql = 'SELECT * FROM ' . $this->getName() . ' WHERE ';
        
        $tmp = array(
            '1' => '1'
        );
        foreach ($where as $key => $value) {
            $tmp[] = $key . '="' . $value . '"';
        }
        
        $tmp2 = array();
        if ($query && strlen($query) > 2) {
            foreach ($this->fields as $field) {
                if ($field->isSearchable()) {
                    $tmp2[] = $field->getName() . ' LIKE "%' . $query . '%"';
                }
            }
        }

        $sql .= implode(' AND ', $tmp);
        
        if (count($tmp2) > 0) {
            $sql .= ' AND (' . implode(' OR ', $tmp2) . ')';
        }
        
        if ($order) {
            $sql .= ' ORDER BY ' . $order;
        }
        
        $sql .= ' LIMIT ' . $limit  . ' OFFSET ' . $offset;
        
        $rows = $this->getDb()->fetchAll($sql);
        
        $entities = array();
        
        foreach ($rows as $row) {
            $entity = $this->create();
            foreach ($row as $key => $value) {
                $entity->$key = $value;
            }
            $entities[] = $entity;
        }
        
        return $entities;
    }
    
    public function countBy(array $where, $query)
    {
        $sql = 'SELECT COUNT(id) FROM ' . $this->getName() . ' WHERE ';
        
        $tmp = array(
            '1' => '1'
        );
        foreach ($where as $key => $value) {
            $tmp[] = $key . '="' . $value . '"';
        }
        
        $tmp2 = array();
        if ($query && strlen($query) > 2) {
            foreach ($this->fields as $field) {
                if ($field->isSearchable()) {
                    $tmp2[] = $field->getName() . ' LIKE "%' . $query . '%"';
                }
            }
        }

        $sql .= implode(' AND ', $tmp);
        
        if (count($tmp2) > 0) {
            $sql .= ' AND (' . implode(' OR ', $tmp2) . ')';
        }
        
        return $this->getDb()->fetchOne($sql);
    }

    public function persist(Entity $entity)
    {
        if (!$this->validate($entity)) {
            return false;
        }
        
        $array = $entity->toArray(false);
        $toSaveArray = $array;
        
        foreach ($this->fields as $field) {
            if ($field instanceof Entity\Field\File &&
                    is_array($array[$field->getName()])) {
                
                $filename = $toSaveArray[$field->getName()]['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                
                $tmp = $field->getName() . time() . rand(0, 1000);                
                
                $newName = md5($tmp) . '.' . $ext;
                
                $toSaveArray[$field->getName()] = $newName;
            }
        }
        
        if (!$entity->id) {
            $id = $this->getDb()->insert(
                $this->getName(),
                $toSaveArray
            );
            $entity->id = $id;
        } else {
            $this->getDb()->update(
                $this->getName(),
                $toSaveArray,
                'id=' . $entity->id
            );
        }
        
        $config = Config::getInstance();
        
        if (!is_dir($config->path['uploads_folder'] . '/' . $this->getName())) {
            mkdir($config->path['uploads_folder'] . '/' . $this->getName());
        }
        
        $dir = $config->path['uploads_folder'] . '/' . $this->getName() . '/' . $entity->id;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        
        // verify if there's a file to save
        foreach ($this->fields as $field) {
            if ($field instanceof Entity\Field\File && 
                    is_array($array[$field->getName()])) {
                $fieldName = $field->getName();
                
                $newName = $toSaveArray[$fieldName];
                $tmpName = $array[$fieldName]['tmp_name'];
                
                move_uploaded_file(
                    $tmpName, 
                    $dir . '/' . $newName
                );
            }
        }
        
        return $entity->id;
    }
    
    public function delete(Entity $entity)
    {
        $this->getDb()->delete(
            $this->getName(),
            'id=' . (int)$entity->id
        );
        
        $config = Config::getInstance();
        
        $dir = $config->path['uploads_folder'] . '/' . $this->getName() . '/' . $entity->id;
        if (is_dir($dir)) {
            $this->delTree($dir);
        }
    }
    
    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.','..')); 
        foreach ($files as $file) { 
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
        }
        return rmdir($dir); 
    }

    public function toArray()
    {
        $array = array(
            'name' => $this->getName(),
            'label' => array(
                'singular' => $this->getSingularLabel(),
                'plural' => $this->getPluralLabel(true)
            ),
            'description' => $this->getDescription(),
            'parents' => $this->getParents(),
            'children' => $this->getChildren(),
            'fields' => array()
        );

        foreach ($this->fields as $field) {
            $array['fields'][] = $field->toArray();
        }

        return $array;
    }
}
