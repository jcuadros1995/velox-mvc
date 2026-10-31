<?php

class PublicPageRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(?string $type = null): array
    {
        $sql = "SELECT id, slug, page_type, title, meta_title, meta_description, h1, is_published, created_at, updated_at FROM site_pages";
        $params = [];
        if ($type !== null) {
            $sql .= " WHERE page_type = :type";
            $params['type'] = $type;
        }
        $sql .= " ORDER BY updated_at DESC, title ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM site_pages WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = "SELECT * FROM site_pages WHERE slug = :slug";
        if ($publishedOnly) $sql .= " AND is_published = 1";
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => trim($slug, '/')]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function save(array $data): int
    {
        $id = (int)($data['id'] ?? 0);
        if ($id > 0) {
            $stmt = $this->db->prepare("UPDATE site_pages SET slug=:slug,page_type=:page_type,title=:title,meta_title=:meta_title,meta_description=:meta_description,h1=:h1,content_html=:content_html,is_published=:is_published WHERE id=:id");
            $stmt->execute([
                'slug'=>$data['slug'],'page_type'=>$data['page_type'],'title'=>$data['title'],
                'meta_title'=>$data['meta_title'],'meta_description'=>$data['meta_description'],
                'h1'=>$data['h1'],'content_html'=>$data['content_html'],'is_published'=>$data['is_published'],'id'=>$id
            ]);
            return $id;
        }
        $stmt = $this->db->prepare("INSERT INTO site_pages(slug,page_type,title,meta_title,meta_description,h1,content_html,is_published) VALUES(:slug,:page_type,:title,:meta_title,:meta_description,:h1,:content_html,:is_published)");
        $stmt->execute([
            'slug'=>$data['slug'],'page_type'=>$data['page_type'],'title'=>$data['title'],
            'meta_title'=>$data['meta_title'],'meta_description'=>$data['meta_description'],
            'h1'=>$data['h1'],'content_html'=>$data['content_html'],'is_published'=>$data['is_published']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function toggle(int $id, int $published): bool
    {
        $stmt = $this->db->prepare("UPDATE site_pages SET is_published=:published WHERE id=:id");
        $stmt->execute(['published'=>$published ? 1 : 0, 'id'=>$id]);
        return $stmt->rowCount() > 0;
    }

    public function createContact(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO contact_messages(name,email,phone,subject,message) VALUES(:name,:email,:phone,:subject,:message)");
        $stmt->execute([
            'name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone'] ?: null,
            'subject'=>$data['subject'] ?: null,'message'=>$data['message']
        ]);
        return (int)$this->db->lastInsertId();
    }
}
