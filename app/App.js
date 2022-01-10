import { NavigationContainer } from '@react-navigation/native';

import { createStackNavigator } from '@react-navigation/stack';

const Stack = createStackNavigator();

// screens
import Hub from './screens/Hub';
import About from './screens/About';

export default function App() {
	return (
		<NavigationContainer>
			<Stack.Navigator>
				<Stack.Screen name='Hub' component={Hub} />
				<Stack.Screen name='About' component={About} />
			</Stack.Navigator>
		</NavigationContainer>
	);
}
