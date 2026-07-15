<?php

namespace A2billing;

class Comment
{
    public const CUSTOMER = 0;
    public const ADMIN = 1;
    public const AGENT = 2;

    private int $id;
    private string $description;
    private string $creationdate;
    private string $creatorname;
    private bool $viewed_cust;
    private bool $viewed_agent;
    private bool $viewed_admin;

    public function __construct(int $id, string $desc, string $date, bool $viewed_cust, bool $viewed_agent, bool $viewed_admin)
    {
        $this->id = $id;
        $this->description = $desc;
        $this->creationdate = $date;
        $this->viewed_cust = $viewed_cust;
        $this->viewed_agent = $viewed_agent;
        $this->viewed_admin = $viewed_admin;
    }

    public static function getComment(int $id): ?self
    {
        $result = Connection::getConnection("cc_ticket_comment")
            ->where("id", $id)
            ->first();
        if (!$result) {
            return null;
        }

        $comment = new self(
            (int)$result["id"],
            $result["description"],
            $result["date"],
            (bool)$result["viewed_cust"],
            (bool)$result["viewed_agent"],
            (bool)$result["viewed_admin"]
        );
        $creatorid = (int)$result["creator"];
        $creator_type = (int)$result["creator_type"];

        if (!empty($creatorid)) {
            if ($creator_type === self::ADMIN) {
                $user = Connection::getConnection("cc_ui_authen")
                    ->where("userid", $creatorid)
                    ->first();
                if ($user) {
                    $comment->setCreatorname(_("(ADMINISTRATOR) ") . $user["name"]);
                }
            } elseif ($creator_type === self::CUSTOMER) {
                $user = Connection::getConnection("cc_card")
                    ->where("id", $creatorid)
                    ->first();
                if ($user) {
                    $comment->setCreatorname($user["lastname"] . " " . $user["firstname"]);
                }
            } elseif ($creator_type === self::AGENT) {
                $user = Connection::getConnection("cc_agent")
                    ->where("id", $creatorid)
                    ->first();
                if ($user) {
                    $comment->setCreatorname(_("(AGENT)") . " " . $user["firstname"] . " " . $user["lastname"]);
                }
            }
        }

        return $comment;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * I think this actually returns if it's *not* viewed?
     *
     * @param int $type
     * @return bool
     */
    public function getViewed(int $type): bool
    {
        switch ($type) {
            case self::CUSTOMER:
                return $this->viewed_cust;
            case self::ADMIN:
                return $this->viewed_admin;
            case self::AGENT:
                return $this->viewed_agent;
            default :
                return false;
        }
    }

    public function markViewed(int $type): bool
    {
        switch ($type) {
            case self::CUSTOMER:
                $value = ["viewed_cust" => 0];
                break;
            case self::ADMIN:
                $value = ["viewed_admin" => 0];
                break;
            case self::AGENT:
                $value = ["viewed_agent" => 0];
                break;
            default:
                return false;
        }

        return Connection::getConnection("cc_ticket_comment")
            ->where("id", $this->id)
            ->update($value) > 0;
    }

    public function getCreationdate()
    {
        return substr($this->creationdate, 0, 19);
    }

    public function setCreatorname($creatorname)
    {
        $this->creatorname = $creatorname;
    }

    public function getCreatorname(): string
    {
        return $this->creatorname;
    }

}
