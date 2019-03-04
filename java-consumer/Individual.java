package ie.tcd.guineec.inventorymanager;

public class Individual {
    private int id;
    private String name;
    private int created_by;

    public Individual(int id, String name, int created_by) {
        this.id = id;
        this.name = name;
        this.created_by = created_by;
    }

    public int getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public int getCreatedBy() {
        return created_by;
    }
}
