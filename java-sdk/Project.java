package ie.tcd.guineec.inventorymanager;

import java.sql.Date;

public class Project {
    private int id;
    private Date end_date;
    private String name;
    private int created_by;

    public Project(int id, Date end_date, String name, int created_by) {
        this.id = id;
        this.end_date = end_date;
        this.created_by = created_by;
        this.name = name;
    }

    public int getId() {
        return id;
    }

    public String getEndDate() {
        return end_date.toString();
    }

    public String getName() {
        return name;
    }

    public int getCreatedBy() {
        return created_by;
    }
}
