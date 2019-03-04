package ie.tcd.guineec.inventorymanager;

public class Equipment {
    private int id;
    private String barcode;
    private Project projectUsing;
    private Individual indResponsible;
    private String description;
    private boolean damaged;
    private User createdBy;

    public Equipment(int id, String barcode, Project projectUsing, Individual indResponsible, String description, boolean damaged, User createdBy) {
        this.id = id;
        this.barcode = barcode;
        this.projectUsing = projectUsing;
        this.indResponsible = indResponsible;
        this.description = description;
        this.createdBy = createdBy;
        this.damaged = damaged;
    }

    public int getId() {
        return id;
    }

    public String getBarcode() {
        return barcode;
    }

    public Project getProjectUsing() {
        return projectUsing;
    }

    public Individual getIndResponsible() {
        return indResponsible;
    }

    public String getDescription() {
        return description;
    }

    public boolean getDamaged() {
        return damaged;
    }

    public User getCreatedBy() {
        return createdBy;
    }
}