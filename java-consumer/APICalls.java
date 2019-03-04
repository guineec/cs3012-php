/*
* A class to deal with making calls to the API
*/
        package ie.tcd.guineec.inventorymanager;

        import android.util.Log;

        import com.google.gson.GsonBuilder;
        import com.google.gson.JsonArray;
        import com.google.gson.JsonElement;
        import com.google.gson.JsonObject;
        import com.google.gson.JsonParser;
        import com.google.gson.Gson;

        import java.io.*;
        import java.net.HttpURLConnection;
        import java.net.URL;
        import java.net.URLEncoder;
        import java.nio.charset.Charset;
        import java.util.HashMap;
        import java.util.Map;

public class APICalls {
    public static final String BASE_URL = "http://project-tests.eu.pn/api/api.php?";
    private static Gson g = new Gson();

    private static String buildUrl(HashMap<String, String> params) {
        String url = "";
        Map<String, String> m = params;
        boolean firstEntry = true;
        for (Map.Entry<String, String> entry : m.entrySet()) {
            if (firstEntry) {
                url = url + entry.getKey() + "=" + entry.getValue();
                firstEntry = false;
            } else {
                try {
                    url = url + "&" + entry.getKey() + "=" + (URLEncoder.encode(entry.getValue(), "UTF-8").replace("+", "%20"));
                } catch (Exception e) {
                    return null;
                }
            }
        }
        return url;
    }

    private static String getResponse(HashMap<String, String> params) {
        try {
            URL url = new URL(BASE_URL + buildUrl(params));
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.connect();
            if (conn.getResponseCode() != 200) {
                return null;
            } else {
                BufferedReader read = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                String response = "";
                String line;
                while ((line = read.readLine()) != null) {
                    response = response + line;
                }
                return response;
            }
        } catch (IOException e) {
            System.out.println("IO Exception when opening connection");
            return null;
        }
    }

    private static String postResponse(HashMap<String, String> params) {
        String builtParams = buildUrl(params);
        byte[] content = builtParams.getBytes(Charset.forName("UTF-8"));
        int contLength = content.length;
        try {
            URL url = new URL(BASE_URL);
            HttpURLConnection  conn = (HttpURLConnection) url.openConnection();
            conn.setDoOutput(true);
            conn.setInstanceFollowRedirects(false);
            conn.setRequestMethod("POST");
            conn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
            conn.setRequestProperty("charset", "utf-8");
            conn.setRequestProperty("Content-Length", Integer.toString(contLength));
            conn.setUseCaches(false);
            DataOutputStream post = new DataOutputStream(conn.getOutputStream());
            post.write(content);
            BufferedReader br = new BufferedReader(new InputStreamReader(conn.getInputStream()));
            String response = "";
            String line = "";
            while((line = br.readLine()) != null) {
                response = response + line;
            }
            return response;
        } catch(Exception e) {
            return null;
        }
    }

    private static JsonArray getResponseAsJsonArray(String response) {
        if (response != null) {
            JsonObject obj = new JsonParser().parse(response).getAsJsonObject();
            String respCode = obj.get(("status_code")).getAsString();
            if (respCode.equals("200")) {
                JsonArray e = obj.getAsJsonArray("data");
                for(JsonElement j : e) {
                    Log.d("API", j.toString());
                }
                return e;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    private static JsonObject getResponseAsJsonObject(String response) {
        if (response != null) {
            return new JsonParser().parse(response).getAsJsonObject();
        } else {
            return null;
        }
    }

    //Retrieval calls
    public static boolean ping() {
        String op = "ping";
        HashMap<String, String> params = new HashMap<>();
        params.put("op", op);
        JsonParser jp = new JsonParser();
        JsonObject obj = (jp.parse(getResponse(params))).getAsJsonObject();
        JsonElement stat = obj.get("status_message");
        String status = stat.getAsString();
        return status.equals("API connection working");
    }

    public static Equipment[] getObjectsByDate(String endDate, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        String uid = "" + currentUser.getId();
        params.put("op", "listObjectsByDate");
        params.put("endDate", endDate);
        params.put("userId", uid);
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Equipment[] ret = g.fromJson(e, Equipment[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Equipment[] getObjsAttachedToInd(Individual ind, User currentUser) {
        String indId = "" + ind.getId();
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "getObjsAttachedToInd");
        params.put("indId", indId);
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Equipment[] ret = g.fromJson(e, Equipment[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Equipment[] getObjsAttachedToProj(Project project, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "getObjsAttachedToProj");
        params.put("projId", "" + project.getId());
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Equipment[] ret = g.fromJson(e, Equipment[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Project getProjUsing(Equipment object, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "getProjUsing");
        params.put("objId", "" + object.getId());
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Gson dateGson = new GsonBuilder().setDateFormat("yyyy-MM-dd").create();
                Project ret = dateGson.fromJson(e.get(0), Project.class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Individual getIndResponsibleFor(Equipment object, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "getIndResponsibleFor");
        params.put("objId", "" + object.getId());
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Individual ret = g.fromJson(e.get(0), Individual.class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Equipment[] findObjects(String barcode, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "findObjects");
        params.put("barcode", barcode);
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Equipment[] ret = g.fromJson(e, Equipment[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Equipment[] getAllObjects(User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "listObjects");
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Equipment[] ret = g.fromJson(e, Equipment[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Equipment[] getAllDamaged(User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "listDamaged");
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if(response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if(e != null) {
                Equipment[] ret = g.fromJson(e, Equipment[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Individual[] getAllIndividuals(User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "listIndividuals");
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Individual[] ret = g.fromJson(e, Individual[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Individual[] getIndsAttachedToProj(Project proj, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "getIndsAttachedToProj");
        params.put("userId", "" + currentUser.getId());
        params.put("projId", "" + proj.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Individual[] ret = g.fromJson(e, Individual[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    public static Project[] getAllProjects(User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "listProjects");
        params.put("userId", "" + currentUser.getId());
        String response = getResponse(params);
        if (response == null) {
            return null;
        } else {
            JsonArray e = getResponseAsJsonArray(response);
            if (e != null) {
                Gson dateGson = new GsonBuilder().setDateFormat("yyyy-MM-dd").create();
                Project[] ret = dateGson.fromJson(e, Project[].class);
                return ret;
            } else {
                return null;
            }
        }
    }

    //Insertion calls
    public static boolean addProject(String endDate, Individual individualInCharge, String name, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "addProject");
        params.put("projectName", name);
        if (individualInCharge != null) {
            params.put("individualId", "" + individualInCharge.getId());
        }
        params.put("userId", "" + currentUser.getId());
        params.put("endDate", endDate);
        String response = postResponse(params);
        if (response == null) {
            return false;
        } else {
            String statusCode = (getResponseAsJsonObject(response)).get("status_code").getAsString();
            return !(!statusCode.equals("200") || statusCode.equals("404"));
        }
    }

    public static boolean addIndividual(String indName, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "addIndividual");
        params.put("indName", indName);
        params.put("userId", "" + currentUser.getId());
        String response = postResponse(params);
        if(response == null) {
            return false;
        } else {
            String statusCode = (getResponseAsJsonObject(response).get("status_code")).getAsString();
            return !(!statusCode.equals("200") || statusCode.equals("404"));
        }
    }

    public static boolean attachIndToProj(Individual ind, Project proj) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "attachIndToProj");
        params.put("indId", "" + ind.getId());
        params.put("projId", "" + proj.getId());
        String response = postResponse(params);
        if(response == null) {
            return false;
        } else {
            String statusCode = (getResponseAsJsonObject(response).get("status_code")).getAsString();
            return !((!statusCode.equals("200")) || statusCode.equals("404"));
        }
    }

    public static boolean addObject(String barcode, Individual ind, Project proj, String description, User currentUser) {
        assert barcode != null && currentUser != null;
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "addObject");
        params.put("barcode", barcode);
        if(ind != null) {
            params.put("indId", "" + ind.getId());
        }
        if(proj != null) {
            params.put("projId", "" + proj.getId());
        }
        if(description != null) {
            params.put("description", description);
        }
        params.put("userId", "" + currentUser.getId());
        String response = postResponse(params);
        if(response == null) {
            return false;
        } else {
            String statusCode = (getResponseAsJsonObject(response).get("status_code")).getAsString();
            return !(!statusCode.equals("200") || statusCode.equals("404"));
        }
    }

    public static boolean attachObjToInd(Equipment object, Individual ind, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "attachObjToInd");
        params.put("objId", "" + object.getId());
        params.put("individualId", "" + ind.getId());
        params.put("userId", "" + currentUser.getId());
        String response = postResponse(params);
        if(response == null) {
            return false;
        } else {
            String statusCode = (getResponseAsJsonObject(response).get("status_code")).getAsString();
            return !((!statusCode.equals("200")) || statusCode.equals("404"));
        }
    }

    public static boolean attachObjToProj(Equipment object, Individual ind, Project proj, User currentUser) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "attachObjToProj");
        params.put("objId", "" + object.getId());
        params.put("individualId", "" + ind.getId());
        params.put("projId", "" + proj.getId());
        params.put("userId", "" + currentUser.getId());
        String response = postResponse(params);
        if(response == null) {
            return false;
        } else {
            String statusCode = (getResponseAsJsonObject(response).get("status_code")).getAsString();
            return statusCode.equals("200");
        }
    }

    public static boolean markAsDamaged(Equipment object, User currentUser) {
        HashMap<String, String> params = new HashMap<String, String>();
        params.put("op", "markDamaged");
        params.put("objId", "" + object.getId());
        params.put("userId", "" + currentUser.getId());
        String response = postResponse(params);
        if(response == null) {
            return false;
        } else {
            String statusCode = (getResponseAsJsonObject(response).get("status_code")).getAsString();
            return statusCode.equals("200");
        }
    }

    public static boolean markAsFixed(Equipment object, User currentUser) {
        HashMap<String, String> params = new HashMap<String, String>();
        params.put("op", "markFixed");
        params.put("objId", "" + object.getId());
        params.put("userId", "" + currentUser.getId());
        String response = postResponse(params);
        if(response == null) {
            return false;
        } else {
            String statusCode = (getResponseAsJsonObject(response).get("status_code")).getAsString();
            return statusCode.equals("200");
        }
    }

    public static boolean addUser(String name, String email, String password) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "addUser");
        params.put("userName", name);
        params.put("userEmail", email);
        params.put("pass", password);
        String response = postResponse(params);
        if(response == null) {
            return false;
        } else {
            if(getResponseAsJsonObject(response).get("status_message").equals("duplicate")) {
                Log.d("API", "Duplicate exists");
                return false;
            }
            String statusCode = (getResponseAsJsonObject(response).get("status_code")).getAsString();
            return !((!statusCode.equals("200")) || statusCode.equals("404"));
        }
    }

    public static User login(String email, String pass) {
        HashMap<String, String> params = new HashMap<>();
        params.put("op", "login");
        params.put("userEmail", email);
        params.put("pass", pass);
        String response = postResponse(params);
        Log.d("REsponse", response);
        if(response == null) {
            return null;
        }
        else {
            JsonObject j = getResponseAsJsonObject(response);
            JsonElement stat = j.get("status_message");
            if(stat.getAsString().equals("login success")) {
                j = j.getAsJsonObject("data");
                User returnUser = g.fromJson(j, User.class);
                Log.d("User", returnUser.getId() + " " + returnUser.getName() + " " + returnUser.getEmail());
                Log.d("HALP", returnUser.getName() + " " + returnUser.getEmail() + " " + returnUser.getId());
                return returnUser;
            } else {
                return null;
            }
        }
    }
}
