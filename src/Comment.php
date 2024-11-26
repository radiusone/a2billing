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
        $DBHandle = DbConnect();
        $result = (new Table("cc_ticket_comment"))->getRow($DBHandle, ["id" => $id]);
        if (!$result) {
            return null;
        }

        $comment = new self(
            $result["id"],
            $result["description"],
            $result["date"],
            $result["viewed_cust"],
            $result["viewed_agent"],
            $result["viewed_admin"]
        );
        $creatorid = (int)$result["creator"];
        $creator_type = (int)$result["creator_type"];

        if (!empty($creatorid)) {
            if ($creator_type === self::ADMIN) {
                $user = (new Table("cc_ui_authen"))->getRow($DBHandle, ["userid" => $creatorid]);
                if ($user) {
                    $comment->setCreatorname(_("(ADMINISTRATOR) ") . $user["name"]);
                }
            } elseif ($creator_type === self::CUSTOMER) {
                $user = (new Table("cc_card"))->getRow($DBHandle, ["id" => $creatorid]);
                if ($user) {
                    $comment->setCreatorname($user["lastname"] . " " . $user["firstname"]);
                }
            } elseif ($creator_type === self::AGENT) {
                $user = (new Table("cc_agent"))->getRow($DBHandle, ["id" => $creatorid]);
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
                return $this->viewed_agent;
            case self::AGENT:
                return $this->viewed_admin;
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
        $dbHandle = DbConnect();

        return (new Table("cc_ticket_comment"))
            ->updateRow($dbHandle, $value, ["id" => $this->id]);
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
