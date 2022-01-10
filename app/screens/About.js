import { View, Text, ScrollView, Button, StyleSheet } from 'react-native';

const About = ({ navigation }) => {
	return (
		<View style={styles.container}>
			<Button title='página Hub' onPress={() => navigation.navigate('Hub')} />
		</View>
	);
};

export default About;

const styles = StyleSheet.create({
	container: {
		flex: 1,
		// backgroundColor: 'gray',
		// alignItems: 'center',
		// justifyContent: 'center',
	},
	box: {
		backgroundColor: 'orange',
		width: '100%',
		height: 200,
		marginBottom: 7,
	},
});
