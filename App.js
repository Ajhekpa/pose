import 'react-native-gesture-handler';
import { StyleSheet } from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { createBottomTabNavigator } from "@react-navigation/bottom-tabs";
import { NavigationContainer, useNavigation } from "@react-navigation/native";
import { WebView } from "react-native-webview";
import { useEffect, useRef } from "react";
import React, { useState, createContext, useContext } from 'react';
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { Button, Text, Platform } from "react-native";


// import { createDrawerNavigator } from '@react-navigation/drawer';
import {PermissionsAndroid} from 'react-native';


PermissionsAndroid.request(PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS);


Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: false,
  }),
});


// async function registerForPushNotificationsAsync() {
//   let token;
//   if (Device.isDevice) {
//     const { status: existingStatus } = await Notifications.getPermissionsAsync();
//     let finalStatus = existingStatus;
//     if (existingStatus !== 'granted') {
//       const { status } = await Notifications.requestPermissionsAsync();
//       finalStatus = status;
//     }
//     if (finalStatus !== 'granted') {
//       alert('Failed to get push token for push notification!');
//       return;
//     }
//     token = await Notifications.getExpoPushTokenAsync();
//   } else {
//     alert('Must use physical device for Push Notifications');
//   }

//   if (token) {
//     console.log("Push token: ", token.data);
//   }

//   return token?.data;
// }


// Création d'un contexte pour la gestion de l'état de connexion
const LoginContext = createContext();


// Écran de connexion modifié pour utiliser LoginContext
function LoginScreen() {
  const { setIsLoggedIn } = useContext(LoginContext);
  const checkLoginStatus = `
    (function() {
      if (document.body.innerHTML.includes('Photos du moment')) {
        window.ReactNativeWebView.postMessage('loggedIn');
      }
    })();
  `;

  const handleOnMessage = (event) => {
    if (event.nativeEvent.data === 'loggedIn') {
      setIsLoggedIn(true);
    }
  };

  return (
    <WebView
      source={{ uri: ".../pose/home" }}
      injectedJavaScript={checkLoginStatus}
      onMessage={handleOnMessage}
      startInLoadingState={true}
      scalesPageToFit={false}
    />
  );
}


// All Screens in the app

function HomeScreen({ expoPushToken }) {
  console.log("Token received in HomeScreen:", expoPushToken);
  const navigation = useNavigation();
  const webViewRef = useRef(null);

  // Le script initial pour stocker le token dans localStorage
  const injectedJavaScript = `
  (function() {
    console.log('Attempting to inject token');
    if(document.readyState === 'complete') {
      console.log('Page loaded, injecting token');
      localStorage.setItem('expoPushToken', '${expoPushToken}');
      if(document.getElementById('expoPushToken')) {
        document.getElementById('expoPushToken').innerText = '${expoPushToken}';
        console.log('Token injected into span:', '${expoPushToken}');
      } else {
        console.log('Span not found');
      }
    } else {
      console.log('Page not fully loaded');
    }
  })();
`;


  const handleLoad = () => {
    webViewRef.current.injectJavaScript(injectedJavaScript);
  };

  // Utiliser useEffect pour injecter le token dans un élément spécifique de la page
  useEffect(() => {
    if (webViewRef.current && expoPushToken) {
      const script = `
        if(document.getElementById('expoPushToken')) {
          document.getElementById('expoPushToken').innerText = '${expoPushToken}';
          console.log('Token injected into span:', '${expoPushToken}');
        }
      `;
      webViewRef.current.injectJavaScript(script);
    }
  }, [expoPushToken]);

  return (
    <WebView
      ref={webViewRef}
      source={{ uri: ".../pose/home"}}
      javaScriptEnabled={true}
      injectedJavaScript={injectedJavaScript}
      onLoad={handleLoad}
      startInLoadingState={true}
      scalesPageToFit={false}
    />
  );
}


function EntraidesScreen() {
  const navigation = useNavigation();

  return (
    <>
      <WebView source={{ uri: ".../pose/groupes" }}
      startInLoadingState={true} 
      scalesPageToFit={false}/>
     
    </>
  );
}

function PoseScreen() {
  const navigation = useNavigation();

  return (
    <>
      <WebView source={{ uri: ".../pose/types-poses" }}
      startInLoadingState={true}
      scalesPageToFit={false}/>
     
    </>
  );
}

function AnnonceScreen() {
  return (
    <WebView
      source={{ uri: ".../pose/all-notifications" }}
      startInLoadingState={true}
      scalesPageToFit={false}
    />
  );
}

function AccountScreen() {
  const { setIsLoggedIn } = useContext(LoginContext);

  const logoutScript = `
    (function() {
      if (document.body.innerHTML.includes('Se connecter')) {
        window.ReactNativeWebView.postMessage('loggedOut');
      }
    })();
  `;

  const handleOnMessage = (event) => {
    if (event.nativeEvent.data === 'loggedOut') {
      setIsLoggedIn(false);
    }
  };
  return (
    <WebView
      source={{ uri: ".../pose/parametres" }}
      injectedJavaScript={logoutScript}
      onMessage={handleOnMessage}
      startInLoadingState={true}
      scalesPageToFit={false}
    />
  );
}



// TAB NAVIGATOR

const TabNavigator = ({ expoPushToken }) => {
  console.log("Token in TabNavigator:", expoPushToken); // Pour le débogage

const Tab = createBottomTabNavigator();

  return (
     <Tab.Navigator
        screenOptions={({ route }) => ({
          tabBarIcon: ({ focused, color, size }) => {
            let iconName;

            if (route.name === "Home") {
              iconName = focused ? "home" : "home-outline";
            } else if (route.name === "Messagerie") {
              iconName = focused ? "people" : "people-outline";
            } else if (route.name === "Pose") {
              iconName = focused ? "add-circle" : "add-circle-outline";
            } else if (route.name === "Blog") {
              iconName = focused ? "notifications" : "notifications-outline";
            } else if (route.name === "Compte") {
              iconName = focused ? "person" : "person-outline";
            }

            // You can return any component that you like here!
            return <Ionicons name={iconName} size={28} color={color} />;
            
          },
          tabBarStyle:{
            backgroundColor: "#045C97",

          },
          tabBarShowLabel: false,
          unmountOnBlur: true,
          tabBarActiveTintColor: "#FFFFFF",
          tabBarInactiveTintColor: "#FFFFFF",
        })}
      >
        <Tab.Screen
  name="Home"
  children={() => <HomeScreen expoPushToken={expoPushToken} />}
  options={{ headerShown: false }}
/>
        <Tab.Screen
          name="Messagerie"
          component={EntraidesScreen}
          options={{
          headerShown: false, // Hides the header bar
        }} />

<Tab.Screen
  name="Pose"
  component={PoseScreen}
  options={{
    headerShown: false,
    tabBarIcon: ({ focused, color, size }) => {
      // Ajustez la taille de l'icône "Messagerie" comme souhaité
      const iconSize = focused ? 50 : 50;
      return <Ionicons name={focused ? "add-circle" : "add-circle"} size={iconSize} color={color} style= {styles.iconAbove } />;
    },
  }}
/>


        <Tab.Screen
          name="Blog"
          component={AnnonceScreen}
          options={{
          headerShown: false, // Hides the header bar
        }}
         
        />
        <Tab.Screen
          name="Compte"
          component={AccountScreen}
          options={{
          headerShown: false, // Hides the header bar
        }}
         
        />
      </Tab.Navigator>
  );
};


// Root Component APP

export default function App() {
  
  const Tab = createBottomTabNavigator();
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [expoPushToken, setExpoPushToken] = useState('');

  // useEffect(() => {
  //   registerForPushNotificationsAsync().then(token => {
  //     if (token) {
  //       console.log("VISU Expo Push Token:", token); // Ajoutez cette ligne pour le débogage
  //       setExpoPushToken(token);
  //     }
  //   });
  // }, []);
  
  const handleLoginSuccess = () => {
    setIsLoggedIn(true);
  };

  useEffect(() => {
    console.log("Registering for push notifications...");
    registerForPushNotificationsAsync()
      .then((token) => {
        console.log("Push token: ", token);
        setExpoPushToken(token);
      })
      .catch((err) => console.log(err));
  }, []);

  async function registerForPushNotificationsAsync() {
    let token;

    if (Platform.OS === "android") {
      await Notifications.setNotificationChannelAsync("default", {
        name: "default",
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: "#FF231F7C",
      });
    }

    if (Device.isDevice) {
      const { status: existingStatus } =
        await Notifications.getPermissionsAsync();
      let finalStatus = existingStatus;
      if (existingStatus !== "granted") {
        const { status } = await Notifications.requestPermissionsAsync();
        finalStatus = status;
      }
      if (finalStatus !== "granted") {
        alert("Failed to get push token for push notification!");
        return;
      }
      // Learn more about projectId:
      // https://docs.expo.dev/push-notifications/push-notifications-setup/#configure-projectid
      token = (
        await Notifications.getExpoPushTokenAsync({
          projectId: "...",
        })
      ).data;
      console.log(token);
    } else {
      alert("Must use physical device for Push Notifications");
    }

    return token;
  }

  const sendNotification = async () => {
    console.log("Sending push notification...");

    // notification message
    const message = {
      to: expoPushToken,
      sound: "default",
      title: "My first push notification!",
      body: "This is my first push notification made with expo rn app",
    };

    await fetch("https://exp.host/--/api/v2/push/send", {
      method: "POST",
      headers: {
        host: "exp.host",
        accept: "application/json",
        "accept-encoding": "gzip, deflate",
        "content-type": "application/json",
      },
      body: JSON.stringify(message),
    });
  };

  return (
    <LoginContext.Provider value={{ isLoggedIn, setIsLoggedIn }}>
      <NavigationContainer>
        {!isLoggedIn ? <LoginScreen /> : <TabNavigator expoPushToken={expoPushToken}/>}
        {/* <Text style={styles.tokenText}>Token: {expoPushToken}</Text> */}
      </NavigationContainer>
    </LoginContext.Provider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#fff",
    alignItems: "center",
    justifyContent: "center",
  },
  tokenText: {
    fontSize: 16, // Taille de la police
    padding: 10, // Padding autour du texte
    color: 'black', // Couleur du texte
    textAlign: 'center', // Alignement du texte
  },
  iconAbove: {
    position: 'relative',
    top: -15, // Ajustez la valeur selon votre préférence pour le déplacement vers le haut
    color: "#FFFFFF",
    shadowColor: "#000", // Couleur de l'ombre
  shadowOffset: { width: 0, height: 2 }, // Décalage de l'ombre en X et Y
  shadowOpacity: 0.3, // Opacité de l'ombre
  shadowRadius: 2, // Flou de l'ombre
  elevation: 4, // Pour Android, ajoute un effet d'élévation similaire à l'ombre
  },
});
