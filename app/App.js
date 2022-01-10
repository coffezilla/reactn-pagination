import { useState, useRef } from 'react';
import { StatusBar } from 'expo-status-bar';
import { ScrollView, StyleSheet, Text, View, Button } from 'react-native';

//
import PaginationJson from './components/PaginationJson';

export default function App() {
	const [paginationCurrentData, setPaginationCurrentData] = useState([]);
	const jsonData = [
		'1',
		'2',
		'3',
		'4',
		'5',
		'6',
		'7',
		'8',
		'9',
		'10',
		'11',
		'12',
		'13',
		'14',
		'15',
		'16',
		'17',
		'18',
		'19',
		'20',
		'21',
		'22',
		'23',
		'24',
		'25',
		'26',
		'27',
		'28',
		'29',
		'30',
		'31',
		'32',
		'33',
		'34',
		'35',
		'36',
		'37',
		'38',
		'39',
		'40',
		'41',
		'42',
		'43',
		'44',
		'45',
		'46',
		'47',
		'48',
		'49',
		'50',
		'51',
		'52',
		'53',
		'54',
		'55',
		'56',
		'57',
		'58',
		'59',
		'60',
		'61',
		'62',
		'63',
		'64',
		'65',
		'66',
		'67',
		'68',
		'69',
		'70',
		'71',
		'72',
		'73',
		'74',
		'75',
		'76',
		'77',
		'78',
		'79',
		'80',
		'81',
		'82',
		'83',
		'84',
		'85',
		'86',
		'87',
		'88',
		'89',
		'90',
		'91',
		'92',
		'93',
		'94',
		'95',
		'96',
		'97',
		'98',
		'99',
		'100',
		'101',
		'102',
		'103',
		'104',
		'105',
		'106',
		'107',
		'108',
		'109',
		'110',
		'111',
		'112',
		'113',
		'114',
		'115',
		'116',
		'117',
		'118',
		'119',
		'120',
		'121',
		'122',
		'123',
		'124',
		'125',
		'126',
		'127',
		'128',
		'129',
		'130',
		'131',
		'132',
		'133',
		'134',
		'135',
		'136',
		'137',
		'138',
		'139',
		'140',
		'141',
		'142',
		'143',
		'144',
		'145',
		'146',
		'147',
		'148',
		'149',
		'150',
		'151',
		'152',
		'153',
		'154',
		'155',
		'156',
		'157',
		'158',
		'159',
		'160',
		'161',
	];

	// function isCloseToBottom({ layoutMeasurement, contentOffset, contentSize }) {
	// 	return (
	// 		layoutMeasurement.height + contentOffset.y >= contentSize.height - 20
	// 	);
	// }

	// function isCloseToTop({ layoutMeasurement, contentOffset, contentSize }) {
	// 	return contentOffset.y == 0;
	// }

	const [paginationTimestamp, setPaginationTimestamp] = useState(null);

	const reloadAllData = () => {
		const timestamp = new Date().getTime();
		setPaginationTimestamp(timestamp);
	};
	// const reference = useRef();
	const ENDPOINT_URL = 'http://192.168.0.106/api/app';

	return (
		<View style={styles.container}>
			{/* <Text>{JSON.stringify(paginationCurrentData, null, 1)}</Text> */}

			<Button title='voltar para página 1' onPress={reloadAllData} />

			<PaginationJson
				// data='https://www.bhxsites.com.br/playground/api/react-pagination/users.php'
				// data='https://www.bhxsites.com.br/playground/api/react-pagination/users.php?page=1&perpage=10'
				// data={`${ENDPOINT_URL}/content/get-posts?page=1&perpage=20`}
				data={`${ENDPOINT_URL}/content/get-posts`}
				// data={jsonData}
				setData={setPaginationCurrentData}
				pathData='list'
				autoLoad
				saveLocalJson={false}
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

			{/* json local */}
			{/* <PaginationJson
				data={jsonData}
				setData={setPaginationCurrentData}
				perPage={10}
			/> */}

			{/* requisicao toda vez */}
			{/* <PaginationJson
				data='https://www.bhxsites.com.br/playground/api/react-pagination/users.php'
				setData={setPaginationCurrentData}
				saveLocalJson={false}
				pathData='list'
				perPage={2}
			/> */}

			{/* unica requisicao */}
			{/* <PaginationJson
				data='https://www.bhxsites.com.br/playground/api/react-pagination/users.php?page=1&perpage=10'
				setData={setPaginationCurrentData}
				pathData='list'
				perPage={2}
			/> */}

			<StatusBar style='auto' />
		</View>
	);
}

const styles = StyleSheet.create({
	container: {
		flex: 1,
		backgroundColor: 'gray',
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
