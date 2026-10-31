<?php

class EventRepository
{
    private PDO $db;
    public function __construct(){ $this->db=Database::getConnection(); }

    public function create(array $data): int
    {
        $stmt=$this->db->prepare("INSERT INTO event_requests(branch_id,name,email,phone,event_type,event_date,event_time,guests,message) VALUES(:branch_id,:name,:email,:phone,:event_type,:event_date,:event_time,:guests,:message)");
        $stmt->execute([
            'branch_id'=>$data['branch_id'],'name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone'],
            'event_type'=>$data['event_type'],'event_date'=>$data['event_date'],'event_time'=>$data['event_time'],
            'guests'=>$data['guests'],'message'=>$data['message'] ?: null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt=$this->db->prepare("SELECT e.*,b.name AS branch_name,b.code AS branch_code,b.email AS branch_email,b.phone AS branch_phone,b.address AS branch_address FROM event_requests e INNER JOIN branches b ON b.id=e.branch_id WHERE e.id=:id LIMIT 1");
        $stmt->execute(['id'=>$id]); $row=$stmt->fetch(PDO::FETCH_ASSOC); return $row ?: null;
    }

    public function all(?int $branchId=null): array
    {
        $sql="SELECT e.*,b.name AS branch_name,b.code AS branch_code FROM event_requests e INNER JOIN branches b ON b.id=e.branch_id";
        $params=[];
        if($branchId!==null){$sql.=" WHERE e.branch_id=:branch_id";$params['branch_id']=$branchId;}
        $sql.=" ORDER BY e.created_at DESC";
        $stmt=$this->db->prepare($sql);$stmt->execute($params);return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id,string $status): bool
    {
        $stmt=$this->db->prepare("UPDATE event_requests SET status=:status WHERE id=:id");
        $stmt->execute(['status'=>$status,'id'=>$id]); return $stmt->rowCount()>0;
    }

    public function branchAdmins(int $branchId): array
    {
        $stmt=$this->db->prepare("SELECT u.id,u.first_name,u.last_name,u.email,b.name AS branch_name,r.name AS role_name FROM users u INNER JOIN roles r ON r.id=u.role_id INNER JOIN branches b ON b.id=u.branch_id WHERE u.branch_id=:branch_id AND u.is_active=1 AND r.name='admin' ORDER BY u.id ASC");
        $stmt->execute(['branch_id'=>$branchId]); return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function superAdmins(): array
    {
        $stmt=$this->db->query("SELECT u.id,u.first_name,u.last_name,u.email,NULL AS branch_name,r.name AS role_name FROM users u INNER JOIN roles r ON r.id=u.role_id WHERE u.is_active=1 AND r.name='superadmin' ORDER BY u.id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
