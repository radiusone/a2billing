<?php

namespace A2billing;

class Ticket
{
    public const STATUS_NEW = 0;
    public const STATUS_FIXED = 1;
    public const STATUS_REOPEN = 2;
    public const STATUS_CLOSED = 3;
    public const STATUS_INVALID = 4;

    public const CUSTOMER = 0;
    public const AGENT = 1;
    public const ADMIN = 2;

    public const PRIORITY_LOW = 1;
    public const PRIORITY_MED = 2;
    public const PRIORITY_HIGH = 3;

    private int $id;
    private string $title;
    private string $description;
    private int $creatorid;
    private int $creator_type;
    private string $creator_login;
    private string $creator_name;
    private string $creator_firstname;
    private string $creator_lastname;
    private string $creator_language;
    private string $creator_email;
    private int $priority;
    private string $creationdate;
    private int $status;
    private int $componentid;
    private string $componentname;
    private bool $viewed_cust;
    private bool $viewed_agent;
    private bool $viewed_admin;
    private ?string $supportbox_email = null;
    private string $supportbox_language;

    public function __construct(int $id)
    {
        $value = (new Table("cc_ticket"))->getRow(["id" => $id]);
        if (count($value)) {
            $this->id = (int)$value["id"];
            $this->creatorid = (int)$value["creator"];
            $this->description = $value["description"];
            $this->priority = $value["priority"];
            $this->title = $value["title"];
            $this->status = (int)$value["status"];
            $this->creationdate = $value["creationdate"];
            $this->componentid = (int)$value["id_component"];
            $this->viewed_admin = (bool)$value["viewed_admin"];
            $this->viewed_agent = (bool)$value["viewed_agent"];
            $this->viewed_cust = (bool)$value["viewed_cust"];
        }

        if (!empty($this->creatorid)) {
            $this->creator_type = (int)$value["creator_type"];
            switch ($this->creator_type) {
                case self::CUSTOMER:
                    $value = (new Table("cc_card"))->getRow(["id" => $this->creatorid]);
                    if (count($value)) {
                        $this->creator_name = $value["lastname"] . " " . $value["firstname"];
                        $this->creator_login = $value["username"];
                        $this->creator_firstname = $value["firstname"];
                        $this->creator_lastname = $value["lastname"];
                        $this->creator_language = $value["language"];
                        $this->creator_email = $value["email"];
                    }
                    break;
                case self::AGENT:
                    $value = (new Table("cc_agent"))->getRow(["id" => $this->creatorid]);
                    if (count($value)) {
                        $this->creator_name = _("(AGENT)") . " " . $value["firstname"] . " " . $value["lastname"];
                        $this->creator_login = $value["login"];
                        $this->creator_firstname = $value["firstname"];
                        $this->creator_lastname = $value["lastname"];
                        $this->creator_language = $value["language"];
                        $this->creator_email = $value["email"];
                    }
                    break;
            }
        }

        if (!empty($this->componentid)) {
            $component_table = new Table(
                "cc_support_component",
                "cc_support_component.name,email,language",
                ["cc_support" => ["id_support", "cc_support.id"]]
            );
            $value = $component_table->getRow(["cc_support_component.id" => $this->componentid]);

            if (count($value)) {
                $this->componentname = $value["name"];
                $this->supportbox_email = $value["email"];
                $this->supportbox_language = $value["language"];
            }
        }
    }

    public static function getTicket(int $id): ?self
    {
        $result = (new Table("cc_ticket"))->getRow(["id" => $id]);
        if (!$result) {
            return null;
        }

        return new self($result["id"]);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatorId(): int
    {
        return $this->creatorid;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): bool
    {
        if (!self::getStatusDisplay($status)) {
            return false;
        }

        return (new Table("cc_ticket"))->updateRow(["status" => $status]);
    }

    /**
     * Returns true if the ticket is *not* viewed (why???)
     *
     * @param $type
     * @return bool
     */
    public function getViewed($type): bool
    {
        switch ($type) {
            case self::CUSTOMER:
                return $this->viewed_cust;
            case self::AGENT:
                return $this->viewed_agent;
            case self::ADMIN:
                return $this->viewed_admin;
            default :
                return true;
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

        return (new Table("cc_ticket"))
            ->updateRow($value, ["id" => $this->id]);

    }

    public function getPriorityDisplay(): string
    {
        return Ticket::DisplayPriority($this->priority);
    }

    public static function DisplayPriority($prior): string
    {
        switch ($prior) {
            case self::PRIORITY_LOW:
                return _("LOW");
            case self::PRIORITY_MED:
                return _("MEDIUM");
            case self::PRIORITY_HIGH:
                return _("HIGH");
            default :
                return _("NONE");
        }
    }

    public function getComponentid(): int
    {
        return $this->componentid;
    }

    public function getComponentname(): string
    {
        return $this->componentname;
    }

    public function getCreationdate(): string
    {
        return substr($this->creationdate, 0, 19);
    }

    public function getCreatorname(): string
    {
        return $this->creator_name;
    }

    /**
     * @return Comment[]
     */
    public function loadComments(): array
    {
        $result = [];
        $return = (new Table("cc_ticket_comment", "id"))
            ->getRows(["id_ticket" => $this->id], ["date"], "DESC");
        foreach ($return as $value) {
            $result[] = Comment::getComment($value["id"]);
        }

        return $result;
    }

    public function insertComment(string $desc, int $creator, int $creator_type)
    {
        $values = ["id_ticket" => $this->id, "description" => $desc, "creator" => $creator, "creator_type" => $creator_type];
        switch ($creator_type) {
            case Comment::CUSTOMER:
                $values["viewed_cust"] = 0;
                break;
            case Comment::ADMIN:
                $values["viewed_admin"] = 0;
                break;
            case Comment::AGENT:
                $values["viewed_agent"] = 0;
                break;
            default:
                return;
        }
        (new Table("cc_ticket_comment"))->addRow($values);

        $owner_comment = "";
        switch ($creator_type) {
            case Comment::CUSTOMER:
                $value = (new Table("cc_card"))->getRow(["id" => $creator]);
                if ($value) {
                    $owner_comment = $value["lastname"] . " " . $value["firstname"];
                }
                break;
            case Comment::ADMIN:
                $value = (new Table("cc_ui_authen"))->getRow(["userid" => $creator]);
                if ($value) {
                    $owner_comment = _("(ADMINISTRATOR) ") . $value["login"];
                }
                break;
            case Comment::AGENT:
                $value = (new Table("cc_agent"))->getRow(["id" => $creator]);
                if ($value) {
                    $owner_comment = _("(AGENT) ") . $value["login"] . " - " . $value["firstname"] . " " . $value["lastname"];
                }
                break;
        }

        $owner = $this->creator_login . " (" . $this->creator_firstname . " " . $this->creator_lastname . ")";
        if ($this->supportbox_email) {
            $this->sendNotification($this->supportbox_email, $this->supportbox_language, $owner, $desc, $owner_comment);
        }
        $this->sendNotification($this->creator_email, $this->creator_language, $owner, $desc, $owner_comment);
    }

    private function sendNotification(string $to, string $lang, string $owner, string $desc, string $owner_comment): void
    {
        try {
            $mail = new Mail(Mail::$TYPE_TICKET_MODIFY, null, $lang);
            $mail->replaceInEmail(Mail::$TICKET_OWNER_KEY, $owner);
            $mail->replaceInEmail(Mail::$TICKET_NUMBER_KEY, $this->id);
            $mail->replaceInEmail(Mail::$TICKET_DESCRIPTION_KEY, $this->description);
            $mail->replaceInEmail(Mail::$TICKET_PRIORITY_KEY, self::DisplayPriority($this->priority));
            $mail->replaceInEmail(Mail::$TICKET_STATUS_KEY, self::getStatusDisplay($this->status));
            $mail->replaceInEmail(Mail::$TICKET_TITLE_KEY, $this->title);
            $mail->replaceInEmail(Mail::$TICKET_COMMENT_DESCRIPTION_KEY, $desc);
            $mail->replaceInEmail(Mail::$TICKET_COMMENT_CREATOR_KEY, $owner_comment);
            $mail->send($to);
        } catch (A2bMailException $e) {
        }
    }

    public static function getStatusDisplay(int $status): string
    {
        $states = self::getAllStatus();

        return $states[$status] ?? "";
    }

    public static function getAllStatus(): array
    {
        return [
            self::STATUS_NEW => _("NEW"),
            self::STATUS_FIXED => _("FIXED"),
            self::STATUS_REOPEN => _("REOPEN"),
            self::STATUS_CLOSED => _("CLOSED"),
            self::STATUS_INVALID => _("INVALID"),
        ];
    }

    public static function getAllStatusListView(): array
    {
        $states = self::getAllStatus();
        array_walk($states, fn (&$v, $k) => $v = [$v, $k]);

        return $states;
    }

    public static function getAllPriority(): array
    {
        return [
            self::PRIORITY_LOW => _("LOW"),
            self::PRIORITY_MED => _("MEDIUM"),
            self::PRIORITY_HIGH => _("HIGH"),
        ];
    }

    public static function getAllPriorityListView(): array
    {
        $pris = self::getAllPriority();
        array_walk($pris, fn (&$v, $k) => $v = [$v, $k]);

        return $pris;
    }

    public static function getPossibleStatus(int $initialStatus, bool $isadmin = false): array
    {
        $result = [["id" => $initialStatus, "name" => self::getStatusDisplay($initialStatus)]];

        switch ($initialStatus) {
            case self::STATUS_NEW:
            case self::STATUS_REOPEN:
                if ($isadmin) {
                    $result[] = ["id" => self::STATUS_FIXED, "name" => self::getStatusDisplay(self::STATUS_FIXED)];
                    $result[] = ["id" => self::STATUS_INVALID, "name" => self::getStatusDisplay(self::STATUS_INVALID)];
                } else {
                    $result[] = ["id" => self::STATUS_CLOSED, "name" => self::getStatusDisplay(self::STATUS_CLOSED)];
                }
                break;
            case self::STATUS_FIXED:
            case self::STATUS_CLOSED:
                if ($isadmin) {
                    $result[] = ["id" => self::STATUS_REOPEN, "name" => self::getStatusDisplay(self::STATUS_REOPEN)];
                } else {
                    $result[] = ["id" => self::STATUS_CLOSED, "name" => self::getStatusDisplay(self::STATUS_CLOSED)];
                }
                break;
            case self::STATUS_INVALID:
                if ($isadmin) {
                    $result[] = ["id" => self::STATUS_REOPEN, "name" => self::getStatusDisplay(self::STATUS_REOPEN)];
                    $result[] = ["id" => self::STATUS_FIXED, "name" => self::getStatusDisplay(self::STATUS_FIXED)];
                }
        }

        return $result;
    }
}
