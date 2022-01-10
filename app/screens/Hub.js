import { useState } from 'react';
import { View, Text, Button, StyleSheet } from 'react-native';

//
import PaginationJson from '../components/PaginationJson';

const Hub = ({ navigation }) => {
	// config
	const ENDPOINT_URL = 'http://192.168.0.106/api/app';

	// content
	const [paginationCurrentData, setPaginationCurrentData] = useState([]);

	// force reload
	const [paginationTimestamp, setPaginationTimestamp] = useState(null); // hack to force page reload
	const reloadAllData = () => {
		const timestamp = new Date().getTime();
		setPaginationTimestamp(timestamp);
	};

	return (
		<View style={styles.container}>
			<Button
				title='página about'
				onPress={() => navigation.navigate('About')}
			/>
			<Button title='voltar para página 1' onPress={reloadAllData} />

			<PaginationJson
				data={`${ENDPOINT_URL}/content/get-posts`}
				setData={setPaginationCurrentData}
				pathData='list'
				autoLoad
				perPage={4}
				params={`timestamp=${paginationTimestamp}`}
			>
				{paginationCurrentData ? (
					paginationCurrentData.length > 0 ? (
						paginationCurrentData.map((result, index) => {
							return (
								<View style={styles.box} key={index}>
									<Text>Elemento {result}</Text>
								</View>
							);
						})
					) : (
						<Text>Nada</Text>
					)
				) : (
					<Text>Loading</Text>
				)}
			</PaginationJson>
		</View>
	);
};

export default Hub;

const styles = StyleSheet.create({
	container: {
		flex: 1,
	},
	box: {
		backgroundColor: 'orange',
		width: '100%',
		height: 200,
		marginBottom: 7,
	},
});
