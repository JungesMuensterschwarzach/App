package automationtools.strings;

/**
 * @author Pad (03.11.2021)
 */
public class Main {

    public static final String LANG = "de";
    public static final String ENDPOINT = "http://172.16.1.242:3000/strings/"+LANG;
    
    public static void main(String[] args) throws Exception {
        String[] updaters = {
            "JSStringUpdater",
            "PHPStringUpdater",
            "TSStringUpdater"
        };

        for (String updater : updaters) {
            Class<?> clazz = Class.forName("automationtools.strings."+updater);
            clazz.getDeclaredConstructor().newInstance();
        }
    }
    
}
